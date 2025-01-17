<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseOrder;
use App\Models\User;
use App\Models\Plan;
// use Modules\Escort\Database\Factories\OrdersFactory;

class Orders extends BaseOrder
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */


    // protected static function newFactory(): OrdersFactory
    // {
    //     // return OrdersFactory::new();
    // }

    public function escort()
    {
        return $this->belongsTo(User::class, 'escort_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_code','code');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'order_id', 'id', 'status');
    }
}
