<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


// use Modules\Escort\Database\Factories\EscortFactory;

class verify extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table="verify";
    protected $fillable = ['passport_image','selfie_image'];

}
