<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Countries;
use App\Models\Region;
use App\Models\Cities;
use App\Models\Nationality;
use App\Services\Resp;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;

class MasterController extends Controller
{
    public function __construct()
    {
    $this->middleware(AuthMiddleware::class);
    }

public function countries(Request $request){
    $countries = Countries::all();
    return Resp::success(['list' => $countries]);
}

public function regions(Request $request){
    $regions = Region::all();
    return Resp::success(['list' => $regions]);
}

 public function cities(Request $request){
    $cities = Cities::all();
    return Resp::success(['list' => $cities]);
}

public function nationality(Request $request){  
    $nationality = Nationality::all();
    return Resp::success(['list' => $nationality]);
}


public function AddGallary(Request $request)
{
    $currentUser = auth()->user();


    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    $userId = $currentUser->id;

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        // Create the user-specific folder if it doesn't exist
        $userFolder = 'uploads/media/user_'  . $userId;

        if (!File::isDirectory(public_path($userFolder))) {
            File::makeDirectory(public_path($userFolder), 0755, true);
        }

        $image->move(public_path($userFolder), $imageName);
        $imageModel = new Media();
        $imageModel->type = $image->getClientMimeType();
        $imageModel->path = $userFolder . '/' . $imageName; // Save full path
        $imageModel->save();

        return Resp::success(['message' => 'Image uploaded successfully', 'image' => $imageModel], 200);
    }
    return Resp::error(['message' => 'No image file found'], 400);
}


}



