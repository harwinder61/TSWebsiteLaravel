<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Resp; // Ensure this Service exists in your app
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Modules\Escort\app\Models\Profile; // Kept your specific namespace
use Modules\Escort\app\Models\Verify;  // Kept your specific namespace
use App\Models\User;

class DiditController extends Controller
{
    private $diditApiKey;
    private $diditBaseUrl;
    private $diditWorkflowId;

    public function __construct()
    {
        $this->diditApiKey = env('DIDIT_API_KEY');
        $this->diditBaseUrl = env('DIDIT_BASE_URL', 'https://verification.didit.me/v3');
        $this->diditWorkflowId = env('DIDIT_WORKFLOW_ID');
    }

    /**
     * Create a DIDiT verification session
     * POST /api/admin/didit/create-session
     */
    public function createVerificationSession(Request $request)
    {
        try {
            // 1. Validate Input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return Resp::error(['message' => $validator->errors()], 'Validation failed', 422);
            }

            // 2. Check Configuration
            if (empty($this->diditWorkflowId)) {
                return Resp::error(['message' => 'DIDIT_WORKFLOW_ID is not configured in .env'], 'Configuration Error', 500);
            }

            $user = User::findOrFail($request->user_id);

            // 3. Prepare Payload
            $payload = [
                'workflow_id'     => $this->diditWorkflowId,
                'vendor_data'     => (string) $user->id,
                'callback'        => env('APP_URL') . '/api/admin/didit/callback',
                // Remove 'callback_method' entirely, or use lowercase 'post'
                // 'callback_method' => 'post', 
                'metadata'        => [
                    'user_id'  => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                ],
                // 'contact_details' is sometimes required depending on workflow settings
                'contact_details' => [
                    'email' => $user->email,
                ]
            ];

            // 4. Send Request to DIDiT
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->diditApiKey, // Standard Auth header for v3
                'x-api-key'     => $this->diditApiKey,             // Some endpoints might still use this
                'Content-Type'  => 'application/json',
            ])->post($this->diditBaseUrl . '/session/', $payload);

            if (!$response->successful()) {
                Log::error('DIDiT API Error: ' . $response->status(), $response->json());
                return Resp::error(['message' => 'Failed to create verification session'], 'DIDiT API Error', 400);
            }

            $sessionData = $response->json();
            $sessionId   = $sessionData['session_id'] ?? $sessionData['id'] ?? null;
            $sessionUrl  = $sessionData['url'] ?? null;

            if (!$sessionId) {
                return Resp::error(['message' => 'Provider did not return a Session ID'], 'API Error', 500);
            }

            // 5. Update/Create Database Record
            // We use updateOrCreate to ensure we don't duplicate records if the user tries again
            $verify = Verify::updateOrCreate(
                ['escort_id' => $user->id], // Lookup by this column
                [
                    'verified_status'     => 2, // 2 = Pending
                    'didit_session_id'    => $sessionId,
                    'didit_workflow_id'   => $this->diditWorkflowId,
                    'didit_session_token' => $sessionData['session_token'] ?? null,
                    'updated_at'          => now()
                ]
            );

            return Resp::success([
                'session_id'       => $sessionId,
                'verification_url' => $sessionUrl,
                'session_token'    => $sessionData['session_token'] ?? null,
            ], 'Verification session created', 201);

        } catch (\Exception $e) {
            Log::error('DIDiT Create Session Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Handle DIDiT callback after verification
     * POST /api/admin/didit/callback
     */
    public function handleDiditCallback(Request $request)
    {
        try {
            Log::info('DIDiT Callback Received:', $request->all());

            // 1. Retrieve Data (Callbacks are usually POST bodies)
            $sessionId = $request->input('session_id') ?? $request->input('id');
            $status    = $request->input('status');
            $decision  = $request->input('decision'); // Some workflows use 'decision'

            // Fallback to query parameters if POST body is empty
            if (!$sessionId) {
                $sessionId = $request->query('session_id') ?? $request->query('verificationSessionId');
                $status    = $request->query('status');
            }

            if (!$sessionId) {
                return Resp::error(['message' => 'Missing session ID'], 'Bad request', 400);
            }

            // 2. Find the Verification Record
            $verify = Verify::where('didit_session_id', $sessionId)->first();

            // Fallback: Try finding by vendor_data (user_id) if passed
            if (!$verify && $request->input('vendor_data')) {
                $verify = Verify::where('escort_id', $request->input('vendor_data'))->first();
            }

            if (!$verify) {
                return Resp::error(['message' => 'Verification record not found'], 'Not found', 404);
            }

            // 3. Map the Status
            $statusNormalized = strtolower($status ?? $decision ?? '');
            
            // Default to 'Pending' (2)
            $newStatus = 2; 

            if (in_array($statusNormalized, ['approved', 'verified', 'completed'])) {
                $newStatus = 1; // Verified
            } elseif (in_array($statusNormalized, ['declined', 'rejected', 'failed'])) {
                $newStatus = 4; // Rejected
            } elseif (in_array($statusNormalized, ['review', 'in_review'])) {
                $newStatus = 2; // Pending/Review
            }

            // 4. Update Verify Table
            $verify->update([
                'verified_status'    => $newStatus,
                'didit_status'       => $status,
                'didit_completed_at' => now(),
            ]);

            // 5. Update Profile Table (Sync status)
            $profile = Profile::where('escort_id', $verify->escort_id)->first();
            if ($profile) {
                $profile->update(['verified_status' => $newStatus]);
            }

            Log::info("Verification updated for User {$verify->escort_id}: Status {$newStatus}");

            return Resp::success(['message' => 'Callback processed successfully']);

        } catch (\Exception $e) {
            Log::error('DIDiT Callback Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Retrieve verification session status from DIDiT manually
     * GET /api/admin/didit/session-status/{session_id}
     */
    public function getSessionStatus($sessionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->diditApiKey,
                'x-api-key'     => $this->diditApiKey,
            ])->get($this->diditBaseUrl . '/session/' . $sessionId);

            if (!$response->successful()) {
                return Resp::error(['message' => 'Failed to retrieve session'], 'DIDiT API Error', 400);
            }

            return Resp::success($response->json());

        } catch (\Exception $e) {
            Log::error('DIDiT Session Status Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Get list of DIDiT verifications
     * GET /api/admin/didit/verifications
     */
    public function getVerifications(Request $request)
    {
        try {
            // Eager load relationships
            // Ensure your Verify model has: public function user() { return $this->belongsTo(User::class, 'escort_id'); }
            $query = Verify::with(['profile']);
            
            if (method_exists(Verify::class, 'user')) {
                $query->with('user');
            }

            // 1. Filter by Status
            if ($request->has('verified_status') && $request->filled('verified_status')) {
                $statusCodes = explode(',', $request->query('verified_status'));
                $query->whereIn('verified_status', $statusCodes);
            } else {
                // Default view: show all relevant statuses
                $query->whereIn('verified_status', [1, 2, 3, 4]);
            }

            // 2. Search Logic (Grouped OR clauses)
            if ($request->has('s') && $request->filled('s')) {
                $searchTerm = $request->query('s');
                
                $query->where(function ($q) use ($searchTerm) {
                    // Search in User table
                    if (method_exists(Verify::class, 'user')) {
                        $q->whereHas('user', function ($uq) use ($searchTerm) {
                            $uq->where('username', 'like', '%' . $searchTerm . '%')
                               ->orWhere('email', 'like', '%' . $searchTerm . '%');
                        });
                    }
                    // Search in Profile table (e.g. ID or Name)
                    $q->orWhere('escort_id', $searchTerm); 
                });
            }

            // 3. Pagination
            $perPage = (int)$request->query('per_page', 10);
            $verifications = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            $pagination = [
                'total_results' => $verifications->total(), // Correct: Gets total DB count
                'total_pages'   => $verifications->lastPage(),
                'page'          => $verifications->currentPage(),
                'page_size'     => $verifications->perPage(),
            ];

            return Resp::success([
                'verifications' => $verifications->items(),
                'pagination'    => $pagination,
            ]);

        } catch (\Exception $e) {
            Log::error('Get Verifications Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Manually update verification status (admin action)
     * POST /api/admin/didit/update-status/{user_id}
     */
    public function updateVerificationStatus(Request $request, $userId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject,review',
                'notes'  => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return Resp::error(['message' => $validator->errors()], 'Validation failed', 422);
            }

            $statusMap = [
                'approve' => 1,
                'reject'  => 4,
                'review'  => 2,
            ];

            // Use updateOrCreate: This allows admins to manually verify a user 
            // even if the user never started a Didit session.
            $verify = Verify::updateOrCreate(
                ['escort_id' => $userId],
                [
                    'verified_status'   => $statusMap[$request->action],
                    'admin_notes'       => $request->notes,
                    'admin_reviewed_at' => now(),
                ]
            );

            // Sync Profile
            $profile = Profile::where('escort_id', $userId)->first();
            if ($profile) {
                $profile->update(['verified_status' => $verify->verified_status]);
            }

            $actionText = ucfirst($request->action);

            return Resp::success([
                'message'    => "Verification status updated: $actionText",
                'new_status' => $verify->verified_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Update Status Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }
}