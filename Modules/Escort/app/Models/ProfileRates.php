<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Auth\app\Models\AuthUser;
// use Modules\Escort\Database\Factories\ProfileRatesFactory;

class ProfileRates extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'profile_rates';
    protected $casts = [
        '15_min' => 'float',
        '30_min' => 'float',
        '1_hour' => 'float',
        '2_hour' => 'float',
        '4_hour' => 'float',
        'overnight' => 'float',
    ];

    protected $fillable = [
        'escort_id',
        'category',
        '15_min',
        '30_min',
        '1_hour',
        '2_hour',
        '4_hour',
        'overnight',
    ];

    public function user(){
        return $this->belongsTo(AuthUser::class);
    }

}
