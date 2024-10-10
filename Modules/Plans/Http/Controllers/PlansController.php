<?php

namespace Modules\Plans\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\Plans\app\Models\Plans;
use Illuminate\Support\Facades\Validator;
use App\Services\Resp;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data=Plans::all();
        return Resp::success(['list'=>$data]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|',
            'code' => 'required|string|max:255|unique:plans,code',
            'duration_type' => 'required|string|',
            'duration_count'=>'required|integer|',
            'price'=>'required|',
            'description'=>'required|',
        ]);


        if ($validator->fails()) {

            return Resp::error([$validator->errors()]);
        }

        $data =Plans::create([
            'title' => $request->title,
            'code' => $request->code,
            'duration_type' => $request->duration_type,
            'duration_count' => $request->duration_count,
            'price' => $request->price,
            'description' => $request->description,
        ]);
    return Resp::success(['details'=>$data]);
    }

    
}
