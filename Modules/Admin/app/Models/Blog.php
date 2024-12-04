<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class Blog extends Model
{
    use HasFactory;

    protected $table='blog';
    protected $fillable=['title','description','media_id','date','slug'];

    public function media(){
        return $this->belongsTo(Media::class,'media_id','id');
    }
    

}
