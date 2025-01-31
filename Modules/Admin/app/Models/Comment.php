<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;
use App\Models\User;

class Comment extends Model
{
    use HasFactory;

    protected $table='comment';
    protected $fillable=['comment','forum_id','user_id','commentator_id','status','message','parent_comment_id','media_id'];

    public function media(){
        return $this->belongsTo(Media::class,'media_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class,'commentator_id','id');
    }

    public function forum(){
        return $this->belongsTo(Forum::class,'forum_id','id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    // Relationship to get the parent comment (if this is a reply)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }


    

}
