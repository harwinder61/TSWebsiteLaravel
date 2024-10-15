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
        
    }

    
}
