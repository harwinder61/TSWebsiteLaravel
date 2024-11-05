<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class BaseSubscription extends Model
{
    protected $table = 'subscriptions';
    protected $fillable = ['escort_id','order_id','plan_code','status','start_date','end_date','created_by','created_more'];
    protected $hidden=['created_at','updated_at'];
    protected $casts=['start_date'=>'date','end_date'=>'date'];



}