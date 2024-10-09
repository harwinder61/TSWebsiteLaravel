<?php

namespace Modules\Plans\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\Plans\app\Models\Plans;
use Illuminate\Support\Facades\Validator;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data=Plans::all();
        return Response::json(['msg'=>$data]);
        //return view('plans::index');
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

            return Response::json(['error' => $validator->errors()], 422);
        }

        $data =Plans::create([
            'title' => $request->title,
            'code' => $request->code,
            'duration_type' => $request->duration_type,
            'duration_count' => $request->duration_count,
            'price' => $request->price,
            'description' => $request->description,
        ]);
    return Response::json(['msg'=>'pricing  plan created successfully','data'=>$data],201);
        //return view('plans::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('plans::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('plans::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
