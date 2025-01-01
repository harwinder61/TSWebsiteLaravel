<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseProfile;
use Modules\Escort\app\Models\ProfileRates;
use App\Models\Location;
// use Modules\Escort\Database\Factories\ProfileFactory;

class Profile extends BaseProfile
{
    public function rates(){
        return $this->hasMany(ProfileRates::class,'escort_id','escort_id');
    }


public function county (){
    return $this->belongsTo(Location::class,'county_id','id');
}

public function region (){
    return $this->belongsTo(Location::class,'region_id','id');
}

public function city (){
    return $this->belongsTo(Location::class,'city_id','id');
}


}

