<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseProfile;
use Modules\Auth\app\Models\AuthUser;
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

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}


