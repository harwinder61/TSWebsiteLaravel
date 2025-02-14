<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;
use App\Models\User;



class SmsLogs extends Model
{
    use HasFactory;

    protected $table='sms_logs';
    protected $fillable=['phone_number','message','to','from' ,'user_id','status'] ;

 
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
   
}


