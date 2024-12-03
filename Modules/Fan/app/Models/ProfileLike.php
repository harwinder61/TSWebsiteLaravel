<?php

namespace Modules\Fan\app\Models;


use Illuminate\Database\Eloquent\Model;
use Modules\Auth\app\Models\AuthUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProfileLike extends Model
{

protected $table = 'profile_like';
protected $fillable = ['fan_id', 'escort_id', 'is_like'];


}
