<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class EmailLog extends Model
{
    use HasFactory;

    protected $table='email_logs';
    protected $fillable=['subject','message','to'];

  

}   