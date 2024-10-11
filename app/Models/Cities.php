<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Cities extends Model
{
    protected $table = 'locations_cities';
    protected $fillable = ['name'];



    public function country(){
        return $this->belongsTo(Countries::class,'country_id','id');
    }
}