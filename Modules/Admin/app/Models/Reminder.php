<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;

class Reminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
protected $table = 'reminder';
protected $fillable = ['title','description','comment','priority','category_id','admin_id'];

public function category()
{
    return $this->belongsTo(Remindercatagory::class,'category_id','id');
}
 
}
