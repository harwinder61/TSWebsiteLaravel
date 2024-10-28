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
use Modules\Auth\app\Models\AuthUser;       

 



class MediaController extends Controller
{
    public function __construct()
    {
        
    }

    public function getMedia(Request $request)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser) {
            return Resp::error(['error' => 'Unauthorized'], 401);
        }

        $gallery = Media::where('escort_id', $currentUser->id)
                        ->where('type', 'gallary')
                        ->get();

        $privateGallery = Media::where('escort_id', $currentUser->id)
                               ->where('type', 'private_gallery')
                               ->get();

        $promoVideo = Media::where('escort_id', $currentUser->id)
                           ->where('type', 'promo_video')
                           ->first();

        return Resp::success(['list'=>[
            'gallery' => $gallery,
            'private_gallery' => $privateGallery,
            'promo_video' => $promoVideo
        ]]);
    }

public function getGallary(Request $request){
    $currentUser = auth()->user();
    if (!$currentUser) {
        return Resp::error(['error' => 'Unauthorized'], 401);
    }
    $gallary = Media::where('escort_id', $currentUser->id)
                    ->where('type', 'gallary')
                    ->get();
    return Resp::success(['gallary'=>$gallary]);    
}

public function addGallary(Media $media,Request $request)
{
    $currentUser = auth()->user();
    
    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000000',
        'type' => 'required|string|in:gallary,private_gallery',
    ]);

    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
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
        $media->escort_id = $userId;
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
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
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
        
        $existingPromoVideo = Media::where('escort_id', $userId)
                                   ->where('type', 'promo_video')
                                   ->first();
        if ($existingPromoVideo) {
            $existingPromoVideo->path = $userFolder . '/' . $videoName;
            $existingPromoVideo->save();
            $media = $existingPromoVideo;
        } else {
            $media->type = "promo_video";
            $media->path = $userFolder . '/' . $videoName; 
            $media->escort_id = $userId;
            $media->save();
        }
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