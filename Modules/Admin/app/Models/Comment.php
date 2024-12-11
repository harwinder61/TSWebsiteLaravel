<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class Comment extends Model
{
    use HasFactory;

    protected $table='comment';
    protected $fillable=['comment','forum_id','user_id','commentator_id','status','message'];

    public function media(){
        return $this->belongsTo(Media::class,'media_id','id');
    }
    

}
