<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseProfile;
class BaseReviews extends Model
{
    protected $table = 'reviews';
    protected $fillable = [
        'user_id',
        'photo_accuracy',
        'service',
        'clean_liness',
        'location',
        'value_for_money',
        'comment',
        'escort_id'
    ];

    public function profile(){
        return $this->belongsTo(BaseProfile::class,'escort_id','id');
    }
}


