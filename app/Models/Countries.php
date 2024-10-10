<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Region;

class Countries extends Model
{
    protected $table = 'locations_countries';
    protected $fillable = ['name'];

    public function region(){
        return $this->belongsTo(Region::class,'region_id','id');
    }
}