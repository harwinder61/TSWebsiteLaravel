<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Escort\app\Models\Escort;
use App\Models\User;
use Modules\Fan\app\Models\Fan;
// use Modules\Escort\Database\Factories\EscortFactory;

class verify extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table="verify";
    protected $fillable = ['passport_image','selfie_image','verified_status'];


    public function escort()
    {
        return $this->belongsTo(Escort::class, 'escort_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'escort_id', 'id');
    }

    public function fan()
    {
        return $this->belongsTo(User::class, 'fan_id', 'id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'escort_id', 'id');
    }


}
