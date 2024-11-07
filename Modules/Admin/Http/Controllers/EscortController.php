<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Resp;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Models\AuthUser;

class EscortController extends Controller
{
    public function getEscorts(Request $request){
        
        $escorts=AuthUser::with('profile')->where('user_type',2)->get();
        return Resp::success(['list'=>$escorts]);
    }
}