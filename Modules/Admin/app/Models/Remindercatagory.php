<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\app\Models\Reminder;
// use Modules\Admin\Database\Factories\PlanFactory;
class Remindercatagory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
protected $table = 'reminder_category';
protected $fillable = ['name'];
 
}
