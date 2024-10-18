<?php

namespace Modules\Escort\Http\Controllers;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Review\app\Models\Review;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Modules\Users\Entities\User;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use App\MasterData\Gender;
use App\MasterData\Orientation;
use App\MasterData\Ethnicity;
use App\MasterData\Hair;
use App\MasterData\Eyes;
use App\MasterData\BreastsSize;
use App\MasterData\BreastsCup;
use App\MasterData\Butt;
use App\MasterData\Body;
use App\MasterData\CockSize;
use App\MasterData\Languages;
use App\MasterData\ExtraServices;
use App\MasterData\Reference;

class MastersController extends Controller
{
    public function getMasterData()
    {
        
        $data = [
            'gender' => Gender::getData(),
            'orientation' => Orientation::getData(),
            'ethnicity' => Ethnicity::getData(),
            'hair' => Hair::getData(),
            'eyes' => Eyes::getData(),
            'breastsSize' => BreastsSize::getData(),
            'breastsCup' => BreastsCup::getData(),
            'butt' => Butt::getData(),
            'body' => Body::getData(),
            'cockSize' => CockSize::getData(),
            'languages' => Languages::getData(),
            'extraServices' => ExtraServices::getData(),
            'reference' => Reference::getData(),
        ];
        return response()->json($data);
    }
       
}
