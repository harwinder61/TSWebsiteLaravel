<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class SmsTemplates extends Model
{
    use HasFactory;

    protected $table='sms_templates';
    protected $fillable=['type','content','status'];

 
}
