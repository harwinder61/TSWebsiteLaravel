<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;

class Forum extends Model
{
    use HasFactory;
    protected $table = 'forum';
    protected $fillable = ['title','category','description','status','tags','region'] ;

    public function postComments(){
        return $this->hasMany(Comment::class,'forum_id','id');
    }
}


