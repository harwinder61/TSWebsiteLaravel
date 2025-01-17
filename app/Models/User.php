<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Escort\app\Models\ProfileRates;
use Modules\Escort\app\Models\Profile;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'username',
        'email',
        'password',
        'user_type',
        'email_verified',
        'verification_token',
        'firstname',
        'lastname',
        'last_active_at',
        'inactivity_email_sent',
        'others',
        'verification_email_sent',
    ];
    protected $casts=[
        'user_type'=>'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fan_reviews(){
        return $this->hasMany(EscortReviews::class,'user_id','id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */

    public function profile_rates(){
        return $this->hasMany(ProfileRates::class,'escort_id','id');
    }

    public function profile(){
        return $this->hasOne(Profile::class,'escort_id','id');
    }
}
