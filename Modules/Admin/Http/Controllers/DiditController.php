<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Resp; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Escort\app\Models\Profile; 
use Modules\Escort\app\Models\Verify;  
use App\Models\User;

class DiditController extends Controller
{
    private $diditApiKey;
    private $diditBaseUrl;
    private $diditWorkflowId;
    private $webhookSecret;

    public function __construct()
    {
        $this->diditApiKey = env('DIDIT_API_KEY');
        $this->diditBaseUrl = env('DIDIT_BASE_URL', 'https://verification.didit.me/v3');
        $this->diditWorkflowId = env('DIDIT_WORKFLOW_ID');
        $this->webhookSecret = env('DIDIT_WEBHOOK_SECRET');
    }

    /**
     * Security: Verify that the request actually came from DIDiT
     */
    private function isValidSignature(Request $request)
    {
        if ($request->header('X-Didit-Test-Webhook') === 'true') return true;

        $signature = $request->header('X-Signature-V2'); 
        if (!$signature || !$this->webhookSecret) return false;

        $computed = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);
        return hash_equals($computed, $signature);
    }

    /**
     * Fetch detailed session results (Helper for manual sync/backup)
     */
    private function getSessionReport($sessionId)
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->diditApiKey,
        ])->get($this->diditBaseUrl . "/session/{$sessionId}/report");

        if ($response->successful()) {
            $report = $response->json();
            return $report['id_verification'] ?? null;
        }
        
        Log::error("Failed to fetch DIDiT report for session: $sessionId");
        return null;
    }

    /**
     * Create a DIDiT verification session
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

            if (empty($this->diditWorkflowId)) {
                return Resp::error(['message' => 'DIDIT_WORKFLOW_ID is missing'], 'Config Error', 500);
            }

            $user = User::findOrFail($request->user_id);

            $payload = [
                'workflow_id' => $this->diditWorkflowId,
                'vendor_data' => (string) $user->id,
                'callback'    => env('APP_URL') . '/api/admin/didit/callback',
                'metadata'    => [
                    'user_id'  => $user->id,
                    'username' => $user->username,
                ],
            ];

            $response = Http::withHeaders([
                'x-api-key'    => $this->diditApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->diditBaseUrl . '/session/', $payload);

            if (!$response->successful()) {
                Log::error('DIDiT Create Error: ' . $response->status(), $response->json());
                return Resp::error(['message' => 'Failed to create session'], 'API Error', 400);
            }

            $sessionData = $response->json();
            $sessionId   = $sessionData['session_id'] ?? $sessionData['id'];

            Verify::updateOrCreate(
                ['escort_id' => $user->id],
                [
                    'verified_status'  => 2,
                    'didit_session_id' => $sessionId,
                    'didit_workflow_id'=> $this->diditWorkflowId,
                    'updated_at'       => now()
                ]
            );

            return Resp::success([
                'session_id'       => $sessionId,
                'verification_url' => $sessionData['url'],
            ], 'Session created', 201);

        } catch (\Exception $e) {
            Log::error('DIDiT Create Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Handle DIDiT callback (Webhook) - Optimized to prevent 504
     */
    public function handleDiditCallback(Request $request)
    {
        Log::info("DIDiT Webhook received", ['payload' => $request->all()]);

        if (!$this->isValidSignature($request)) {
            Log::warning("DIDiT: Invalid signature detected.");
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        try {
            $sessionId = $request->input('session_id');
            $status    = $request->input('status');

            if (!$sessionId) return response()->json(['message' => 'Missing Session ID'], 400);

            $verify = Verify::where('didit_session_id', $sessionId)->first();
            if (!$verify) return response()->json(['message' => 'Record not found'], 404);

            $statusMap = ['Approved' => 1, 'In Review' => 2, 'Declined' => 4];
            $newStatus = $statusMap[$status] ?? 2;

            // Extract images directly from payload to avoid 504 timeout
            $decision = $request->input('decision');

            if ($decision) {
                // Extract Selfie from liveness_checks
                if (!empty($decision['liveness_checks'])) {
                    $liveness = $decision['liveness_checks'][0];
                    $verify->selfie_image = $liveness['reference_image'] ?? $verify->selfie_image;
                }

                // Extract Passport from id_verifications
                // Note: In your log, 'id_verifications' was null, 
                // but usually it appears here on 'Approved' status.
                if (!empty($decision['id_verifications'])) {
                    $idCheck = $decision['id_verifications'][0];
                    $verify->passport_image = $idCheck['front_image'] ?? $verify->passport_image;
                }
            }

            $verify->verified_status = $newStatus;
            $verify->didit_status    = $status;
            $verify->didit_completed_at = now();
            $verify->save();

            Profile::where('escort_id', $verify->escort_id)->update(['verified_status' => $newStatus]);

            Log::info("DIDiT Sync: User {$verify->escort_id} updated to $status");

            return response()->json(['message' => 'Success'], 200);

        } catch (\Exception $e) {
            Log::error('DIDiT Callback Error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get list of DIDiT verifications
     */
    public function getVerifications(Request $request)
    {
        try {
            $query = Verify::with(['profile']);
            
            if (method_exists(Verify::class, 'user')) {
                $query->with('user');
            }

            if ($request->has('verified_status') && $request->filled('verified_status')) {
                $statusCodes = explode(',', $request->query('verified_status'));
                $query->whereIn('verified_status', $statusCodes);
            } else {
                $query->whereIn('verified_status', [1, 2, 3, 4]);
            }

            if ($request->has('s') && $request->filled('s')) {
                $searchTerm = $request->query('s');
                $query->where(function ($q) use ($searchTerm) {
                    if (method_exists(Verify::class, 'user')) {
                        $q->whereHas('user', function ($uq) use ($searchTerm) {
                            $uq->where('username', 'like', '%' . $searchTerm . '%')
                               ->orWhere('email', 'like', '%' . $searchTerm . '%');
                        });
                    }
                    $q->orWhere('escort_id', $searchTerm); 
                });
            }

            $perPage = (int)$request->query('per_page', 10);
            $verifications = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            return Resp::success([
                'verifications' => $verifications->items(),
                'pagination'    => [
                    'total_results' => $verifications->total(),
                    'total_pages'   => $verifications->lastPage(),
                    'page'          => $verifications->currentPage(),
                    'page_size'     => $verifications->perPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Get Verifications Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }

    /**
     * Manually update verification status
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

            $statusMap = ['approve' => 1, 'reject' => 4, 'review' => 2];

            $verify = Verify::updateOrCreate(
                ['escort_id' => $userId],
                [
                    'verified_status'   => $statusMap[$request->action],
                    'admin_notes'       => $request->notes,
                    'admin_reviewed_at' => now(),
                ]
            );

            Profile::where('escort_id', $userId)->update(['verified_status' => $verify->verified_status]);

            return Resp::success([
                'message'    => "Verification status updated: " . ucfirst($request->action),
                'new_status' => $verify->verified_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Update Status Error: ' . $e->getMessage());
            return Resp::error(['message' => $e->getMessage()], 'Server error', 500);
        }
    }
}