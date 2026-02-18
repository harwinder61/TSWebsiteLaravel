<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Media extends Model
{   
    protected $table = 'media';
    protected $fillable = ['type','path','is_temp','title','description','alternative_text','caption','escort_id','nsfw_status'];

    public function escort(){
        return $this->belongsTo(BaseProfile::class,'escort_id','id');
    }


    public function user(){
        return $this->belongsTo(User::class,'escort_id','id');
    }

    public function media(){
        return $this->hasMany(Media::class,'escort_id','id');
    }
}
