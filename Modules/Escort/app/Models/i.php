<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseSubscription;
use Modules\Auth\Entities\User;
use Modules\Escort\Entities\Plan;
use Modules\Plans\app\Models\Plans;

// use Modules\Escort\Database\Factories\OrdersFactory;

class Subscription extends BaseSubscription
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */


    // protected static function newFactory(): OrdersFactory
    // {
    //     // return OrdersFactory::new();
    // }
    function escort() {
        return $this->belongsTo(User::class, 'escort_id', 'id');
     }

      function plan() {
        return $this->belongsTo(Plans::class, 'code', 'plan_code');
     }

    
}
 