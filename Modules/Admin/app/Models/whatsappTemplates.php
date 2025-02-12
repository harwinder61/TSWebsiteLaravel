<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class whatsappTemplates extends Model
{
    use HasFactory;

    protected $table='whatsapp_templates';
    protected $fillable=['type','content','status'];

 
}
