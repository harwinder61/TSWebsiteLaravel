<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseProfile;
use Modules\Auth\app\Models\AuthUser;
class BaseSettings extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'key','value'
    ];

    protected $casts  =  [
        'value' => 'json'
    ];

    

    
}


