<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
// use Modules\Admin\Database\Factories\PlanFactory;

class Forum extends Model
{
    use HasFactory;
    protected $table = 'forum';
    protected $fillable = ['title','category','description','status','tags','region'] ;

    public function postComments(){
        return $this->hasMany(Comment::class,'forum_id','id');
    }
    public function getAuthor(){
        return $this->belongsTo(User::class,'author_id','id');
    }
}


