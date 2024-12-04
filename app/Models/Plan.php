<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Plans\Database\Factories\PlansFactory;
use App\Models\BaseSubscription;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
   
    protected $casts=[
        'description'=>'json'
    ];

    public function active_users(){
        return $this->hasMany(BaseSubscription::class,'plan_code','code')->where('status',"ACTIVE");
    }

}
