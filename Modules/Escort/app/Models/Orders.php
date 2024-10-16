<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseOrder;
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
}
