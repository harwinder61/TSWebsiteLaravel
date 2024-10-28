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
use Illuminate\Support\Facades\Log;
use App\Models\Plan;
    
class MasterController extends Controller
{

public function countries(Request $request){
    $countries = Countries::with('region')->get();
    return Resp::success(['list' => $countries]);
}

public function regions(Request $request){
    $regions = Region::all();
    return Resp::success(['list' => $regions]);
}

 public function cities(Request $request){
    $cities = Cities::with('country')->get();
    return Resp::success(['list' => $cities]);
}

public function nationality(Request $request){  
    $nationality = Nationality::all();
    return Resp::success(['list' => $nationality]);
}

public function plans(Request $request){
    $data=Plan::all();
    return Resp::success(['list'=>$data]);

}


}



