<?php

namespace Modules\Auth\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use \Illuminate\Auth\Authenticatable;
    // Specify the table name if it doesn't follow Laravel's convention
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'password','user_type'];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts=[
        'user_type'=>'integer',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, representing the custom claims that will be sent with the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
