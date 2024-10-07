<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Escort\Database\Factories\ProfileFactory;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profile';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','escort_id'];

    // protected static function newFactory(): ProfileFactory
    // {
    //     // return ProfileFactory::new();
    // }
}
