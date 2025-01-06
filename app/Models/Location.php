<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Location extends Model
{
    protected $table='locations';
    protected $fillable = ['name','type','parent_id','slug'];
    
   
    public function county(){
        return $this->hasOne(Location::class,'id','parent_id');
    }
}