<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class whatsappLogs extends Model
{
    use HasFactory;

    protected $table='whatsapp_logs';
    protected $fillable=['type','message','to','from'];

 
}
