<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\Resp;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Auth;
 



class MediaController extends Controller
{
    public function __construct()
    {
        
    }

public function addGallary(Media $media,Request $request)
{
    $currentUser = auth()->user();
    
    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000000',
        'type' => 'required|string|in:gallary,private_gallery',
    ]);

    if ($validator->fails()) {
        return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }
    $userId = $currentUser->id;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = $request->input('type') . '_' . time() . '.' . $image->getClientOriginalExtension();
        $userFolder = 'uploads/media/user_'  . $userId;
        if (!File::isDirectory(base_path($userFolder))) {
            File::makeDirectory(base_path($userFolder), 0755, true);
        }
        $image->move(base_path($userFolder), $imageName);
        $media->type = $request->input('type');
        $media->path = $userFolder . '/' . $imageName; 
        $media->save();
        return Resp::success(['message' => 'Image uploaded successfully', 'image' => $media]);
    }
    return Resp::error(['message' => 'No image file found'], 400);
}

public function addPromoVideo(Media $media, Request $request)
{
    $currentUser = auth()->user();
    
    $validator = Validator::make($request->all(), [
     'video' => 'required|mimes:mp4,mov,avi,mkv|max:512000',     
    ]);

    if ($validator->fails()) {
        return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
     }
    $userId = $currentUser->id;
    if ($request->hasFile('video')) {
        $video = $request->file('video');
        $videoName = 'promo_video_' . time() . '.' . $video->getClientOriginalExtension();
        $userFolder = 'uploads/media/user_' . $userId;
        if (!File::isDirectory(base_path($userFolder))) {
            File::makeDirectory(base_path($userFolder), 0755, true);
        }
        $video->move(base_path($userFolder), $videoName);
        $media->type = "promo";
        $media->path = $userFolder . '/' . $videoName; 
        $media->save();
        return Resp::success(['message' => 'Video uploaded successfully', 'video' => $media]);
    }
    return Resp::error(['message' => 'No video file found'], 400);
} 

public function getPromoVideo(Request $request)
{
    $currentUser = auth()->user();
    
    return Resp::success(['details' => $currentUser]);
}

}