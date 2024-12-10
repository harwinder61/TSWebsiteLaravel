<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Resp;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Models\AuthUser;

class FanController extends Controller
{
    public function getFans(Request $request){
        $perPage = $request->query('per_page', 10);
        $fans=AuthUser::with('profile')->where('user_type',1)->paginate($perPage);
        return Resp::success([
            'list'=>$fans->items(),
            'pagination'=>[
                'total_results'=>$fans->total(),
                'total_pages'=>$fans->lastPage(),
                'page_number'=>$fans->currentPage(),
                'page_size'=>$fans->perPage()
            ]
        ]);
    }

    
}
