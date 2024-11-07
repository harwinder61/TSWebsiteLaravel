<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class BaseOrder extends Model
{

    protected $table='orders';
    protected $fillable = ['escort_id','plan_code','start_date','payment_status','end_date','only_fans_link','many_vids_link','fan_centro_link','image_id'];
    protected $hidden=['created_at','updated_at'];
    protected $casts=['start_date'=>'date'];




}