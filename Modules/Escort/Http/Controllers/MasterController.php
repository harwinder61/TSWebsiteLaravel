<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Countries;
use App\Models\Region;
use App\Models\Cities;
use App\Models\Nationality;
use App\Services\Resp;

class MasterController extends Controller
{

public function countries(Request $request){
    Log::info("Countries function here");
    $countries = Countries::all();
    return Resp::success(['list' => $countries]);
}

public function regions(Request $request){
    Log::info("Regions function here");
    $regions = Region::all();
    return Resp::success(['list' => $regions]);
}

 public function cities(Request $request){
    Log::info("Cities function here");
    $cities = Cities::all();
    Log::info("Cities: " . json_encode($cities));
    return Response::json(['cities' => $cities]);
}

public function nationality(Request $request){  
    Log::info("Nationality function here");
    $nationality = Nationality::all();
    Log::info("Nationality: " . json_encode($nationality));
    return Response::json(['nationality' => $nationality]);
}

}
