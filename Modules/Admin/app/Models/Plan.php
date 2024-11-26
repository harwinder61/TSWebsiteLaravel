<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title','price','description','days','allowed_user_account','advert_spaces','checkout_text','desktop_placeholder','mobile_placeholder' ];
    protected $casts=[
        'description'=>'json',
        'allowed_user_account'=>'integer',
        'days'=>'integer',
        'price'=>'decimal:2',
        'allowed_user_account'=>'integer'
    ];

    // protected static function newFactory(): PlanFactory
    // {
    //     // return PlanFactory::new();
    // }
}
