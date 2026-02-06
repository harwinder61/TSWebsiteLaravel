<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Modules\Escort\app\Models\Profile;
use Modules\Escort\app\Models\Verify;
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
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return Resp::error(['message' => $validator->errors()], 'Validation failed', 422);
            }

            $user = User::findOrFail($request->user_id);
            
            // Prepare DIDiT session creation payload
            $payload = [
                'workflow_id' => $this->diditWorkflowId,
                'vendor_data' => $user->id . '_' . $user->username,
                'callback' => env('APP_URL') . '/api/admin/didit/callback',
                'callback_method' => 'both',
                'metadata' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'type' => 'age_verification'
                ],
                'contact_details' => [
                    'email' => $user->email,
                ]
            ];

            // Call DIDiT API to create session
            $response = Http::withHeaders([
                'x-api-key' => $this->diditApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->diditBaseUrl . '/session/', $payload);

            if (!$response->successful()) {
                Log::error('DIDiT API Error: ' . $response->status(), $response->json());
                return Resp::error(['message' => 'Failed to create verification session'], 'DIDiT API Error', 400);
            }

            $sessionData = $response->json();

            // Store DIDiT session info in database
            $verify = Verify::firstOrCreate(
                ['escort_id' => $user->id],
                [
                    'verified_status' => 2, // Pending
                    'didit_session_id' => $sessionData['session_id'],
                    'didit_session_token' => $sessionData['session_token'],
                    'didit_workflow_id' => $this->diditWorkflowId,
                ]
            );

            // Update existing record if needed
            if ($verify->didit_session_id !== $sessionData['session_id']) {
                $verify->update([
                    'didit_session_id' => $sessionData['session_id'],
                    'didit_session_token' => $sessionData['session_token'],
                    'verified_status' => 2,
                ]);
            }

            return Resp::success([
                'session_id' => $sessionData['session_id'],
                'verification_url' => $sessionData['url'],
                'session_token' => $sessionData['session_token'],
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
            Log::info('DIDiT Callback received', $request->all());

            $sessionId = $request->query('verificationSessionId');
            $status = $request->query('status'); // Approved, Declined, In Review

            if (!$sessionId) {
                return Resp::error(['message' => 'Missing session ID'], 'Bad request', 400);
            }

            // Retrieve verification record by session ID
            $verify = Verify::where('didit_session_id', $sessionId)->first();

            if (!$verify) {
                return Resp::error(['message' => 'Verification record not found'], 'Not found', 404);
            }

            // Update verification status based on DIDiT response
            $statusMap = [
                'Approved' => 1,
                'Declined' => 4,
                'In Review' => 2,
            ];

            $newStatus = $statusMap[$status] ?? 2;

            $verify->update([
                'verified_status' => $newStatus,
                'didit_status' => $status,
                'didit_completed_at' => now(),
            ]);

            // Update profile verified status as well
            $profile = Profile::where('escort_id', $verify->escort_id)->first();
            if ($profile) {
                $profile->update(['verified_status' => $newStatus]);
            }

            Log::info('Verification status updated', [
                'user_id' => $verify->escort_id,
                'didit_status' => $status,
                'app_status' => $newStatus,
            ]);

            return Resp::success(['message' => 'Verification processed successfully']);

        } catch (\Exception $e) {
            Log::error('DIDiT Callback Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Retrieve verification session status from DIDiT
     * GET /api/admin/didit/session-status/{session_id}
     */
    public function getSessionStatus($sessionId)
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->diditApiKey,
            ])->get($this->diditBaseUrl . '/session/' . $sessionId);

            if (!$response->successful()) {
                Log::error('DIDiT Session Fetch Error: ' . $response->status());
                return Resp::error(['message' => 'Failed to retrieve session'], 'DIDiT API Error', 400);
            }

            return Resp::success($response->json());

        } catch (\Exception $e) {
            Log::error('DIDiT Session Status Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Get list of DIDiT verifications (replacing old verification list)
     * GET /api/admin/didit/verifications
     */
    public function getVerifications(Request $request)
    {
        try {
            $query = Verify::with(['user', 'profile']);

            // Filter by verified_status
            if ($request->has('verified_status')) {
                $statusCodes = explode(',', $request->query('verified_status'));
                $query->whereIn('verified_status', $statusCodes);
            } else {
                // Default to pending, approved, in review
                $query->whereIn('verified_status', [1, 2, 3, 4]);
            }

            // Search by username or email
            if ($request->has('s')) {
                $searchTerm = $request->query('s');
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('username', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%');
                });
            }

            // Pagination
            $perPage = (int)$request->query('per_page', 10);
            $verifications = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            $pagination = [
                'total_results' => $verifications->count(),
                'total_pages' => $verifications->lastPage(),
                'page' => $verifications->currentPage(),
                'page_size' => $verifications->perPage(),
            ];

            return Resp::success([
                'verifications' => $verifications->items(),
                'pagination' => $pagination,
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
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return Resp::error(['message' => $validator->errors()], 'Validation failed', 422);
            }

            $statusMap = [
                'approve' => 1,
                'reject' => 4,
                'review' => 2,
            ];

            $verify = Verify::where('escort_id', $userId)->first();
            if (!$verify) {
                return Resp::error(['message' => 'Verification record not found'], 'Not found', 404);
            }

            $newStatus = $statusMap[$request->action];
            $verify->update([
                'verified_status' => $newStatus,
                'admin_notes' => $request->notes,
                'admin_reviewed_at' => now(),
            ]);

            // Update profile
            $profile = Profile::where('escort_id', $userId)->first();
            if ($profile) {
                $profile->update(['verified_status' => $newStatus]);
            }

            $actionText = match($request->action) {
                'approve' => 'Approved',
                'reject' => 'Rejected',
                'review' => 'Marked for Review',
            };

            return Resp::success([
                'message' => 'Verification status ' . $actionText,
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Update Status Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }
}
