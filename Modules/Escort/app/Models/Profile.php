<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseProfile;
use Modules\Escort\app\Models\ProfileRates;
// use Modules\Escort\Database\Factories\ProfileFactory;

class Profile extends BaseProfile
{
    public function rates(){
        return $this->hasMany(ProfileRates::class,'escort_id','id');
    }
}
