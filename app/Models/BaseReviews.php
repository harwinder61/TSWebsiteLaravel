<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'comments',
        'escort_id'
    ];
}


