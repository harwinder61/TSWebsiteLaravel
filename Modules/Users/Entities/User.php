<?php

namespace Modules\Users\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Specify the table name if it doesn't follow Laravel's convention
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'user_type'];
    protected $hidden = ['password', 'remember_token'];
}
