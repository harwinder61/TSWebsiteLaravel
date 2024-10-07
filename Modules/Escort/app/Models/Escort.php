<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Escort\Database\Factories\EscortFactory;

class Escort extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table="profile";
    protected $fillable = ['name','escort_id'];

    // protected static function newFactory(): EscortFactory
    // {
    //     // return EscortFactory::new();
    // }
}
