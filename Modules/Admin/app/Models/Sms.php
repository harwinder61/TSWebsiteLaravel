<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;
use App\Models\User;

class Sms extends Model
{
    use HasFactory;

    protected $table='sms_logs';
    protected $fillable=['phone_number','message','to','From' ,'user_id'];

 

   
}


