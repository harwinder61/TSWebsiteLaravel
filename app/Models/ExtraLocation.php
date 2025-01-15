<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseSubscription;
class ExtraLocation extends Model
{
    protected $table = 'extra_locations';
    protected $fillable = [
        'subscription_id','region_id','county_id','city_id'
    ];

    public function subscription(){
        return $this->belongsTo(BaseSubscription::class,'subscription_id','id');
    }

    public function region(){
        return $this->belongsTo(Region::class,'region_id','id');
    }

    public function county(){
        return $this->belongsTo(Region::class,'county_id','id');
    }

    public function city(){
        return $this->belongsTo(Region::class,'city_id','id');
    }

    

    

    
}


