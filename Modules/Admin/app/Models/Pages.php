<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class Pages extends Model
{
    use HasFactory;

    protected $table='pages';
    protected $fillable=['title','description','status','featured_image',];

    public function media(){
        return $this->belongsTo(Media::class,'featured_image','id');
    }
}
