<?php

namespace Modules\Fan\app\Models;

use App\Models\BaseReviews;
use Illuminate\Database\Eloquent\Model;
use Modules\Auth\app\Models\AuthUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FanReviews extends BaseReviews
{

    public function escort(){
        
        return $this->belongsTo(AuthUser::class,'escort_id','id');
    }
    
    // public function fan(){
    //     return $this->belongsTo(AuthUser::class,'user_id','id');
    // }   

}
