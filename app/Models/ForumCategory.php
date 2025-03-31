<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\app\Models\Forum;

class ForumCategory extends Model
{


    protected $table = 'forum_categories';
    protected $fillable = ['name','slug','status'];

    public function forums(){
        return $this->hasMany(Forum::class,'category_id','id');
    }
}