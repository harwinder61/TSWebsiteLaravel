<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseOrder;

class BaseSubscription extends Model
{
    protected $table = 'subscriptions';
    protected $fillable = ['escort_id','order_id','plan_code','status','image_id','start_date','created_by','created_mode','end_date','extra_location','short_order'];
    protected $hidden=['created_at','updated_at'];
    protected $casts=['start_date'=>'date','end_date'=>'date','extra_location'=>'json'];

    public function orders(){
        return $this->belongsTo(BaseOrder::class,'order_id','id');
    }


}