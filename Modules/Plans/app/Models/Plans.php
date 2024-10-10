<?php

namespace Modules\Plans\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Plans\Database\Factories\PlansFactory;

class Plans extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title','code','duration_type','duration_count','price','description'];
    protected $casts=[
        'description'=>'json'
    ];

}
