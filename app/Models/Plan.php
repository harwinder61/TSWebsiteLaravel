<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Plans\Database\Factories\PlansFactory;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
   
    protected $casts=[
        'description'=>'json'
    ];

}
