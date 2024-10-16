<?php

namespace Modules\Auth\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Escort\app\Models\ProfileRates;
use Modules\Escort\app\Models\Profile;
use App\Models\User;

class AuthUser extends User implements JWTSubject
{
    //use \Illuminate\Auth\Authenticatable;
    // Specify the table name if it doesn't follow Laravel's convention
    
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
