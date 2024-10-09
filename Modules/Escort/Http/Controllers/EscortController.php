<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Models\Region;
use App\Models\Cities;
use App\Models\Nationality;
use App\Models\AddGallary;
use App\Services\Resp;
class EscortController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }

    public function getProfile(Request $request)
    {

        $user = auth()->user();
        $data = Profile::where('escort_id', $user->id)->first();
        if (!$data) {

            return Resp::error(['message' => 'No profile found'], 404);
        }
        return Resp::success(['profile' => $data]);
    }
    public function updateProfile(Request $request)
    {

        test();
        $user = auth()->user();
        // Get the user type
        $userType = $user->user_type;

        // Fetch user data based on user type
        if ($userType == 1) {
            return Resp::error(['msg' => 'User type 1 does not have access to update profile', 'user' => $user]); 
        } elseif ($userType == 2) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
                }

            $updated = Profile::where('escort_id', $user->id)
                ->update(['name' => $request->name]);

            if (!$updated) {
                return Resp::error(['error' => 'Failed to update profile'], 500);
            }
            // Find the updated escort profile
            $data = Profile::where('escort_id', $user->id)->get();

            //$data=Escort::find($user->id);
            return Resp::success(['msg' => 'profile name updated successfully', 'data' => $data]);
        } else {
            return Resp::error(['msg' => 'Invalid user type', 'user' => $user]);
        }

        return Resp::error(['msg' => 'No user type found', 'user' => $user]);
    }
}
