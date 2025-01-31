<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Media extends Model
{   
    protected $table = 'media';
    protected $fillable = ['type','path','is_temp','title','description','alternative_text','caption','escort_id'];

    public function escort(){
        return $this->belongsTo(BaseProfile::class,'escort_id','id');
    }


    
}
