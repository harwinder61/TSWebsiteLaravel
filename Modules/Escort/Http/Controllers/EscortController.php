<?php

namespace Modules\Escort\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EscortController extends Controller
{
    public function __construct(){
        $this->middleware(AuthMiddleware::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('escort::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('escort::create');
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
        return view('escort::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('escort::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    public function getProfile(Request $request){

        $user=auth()->user();
        $data=Profile::where('escort_id',$user->id)->first();
        //if($data->isEmpty()){
        //    return Response::json(['message'=>'No profile found'],404);
        //}
        Log::info("get Profile function here");
        if(!$data){

            return Response::json(['message'=>'No profile found'],404);
        }
        

        return Response::json(['profile'=>$data]);


    }
    public function updateProfile(Request $request){

        test();
        $user=auth()->user();
        // Get the user type
        $userType = $user->user_type;

        // Fetch user data based on user type
        if ($userType == 1) {
            return Response::json(['msg'=>'User type 1 does not have access to update profile','user'=>$user]);
            
        } elseif ($userType == 2) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()], 422);
            }

            $updated = Profile::where('escort_id', $user->id)
                ->update(['name' => $request->name]);

            if (!$updated) {
                return Response::json(['error' => 'Failed to update profile'], 500);
            }
            // Find the updated escort profile
            $data = Profile::where('escort_id', $user->id)->get();

            //$data=Escort::find($user->id);
            return Response::json(['msg'=>'profile name updated successfully','data'=>$data]);
        } else {
            return Response::json(['msg'=>'Invalid user type','user'=>$user]);
            
        }

        
        return Response::json(['msg'=>'No user type found','user'=>$user]);
    }
}
