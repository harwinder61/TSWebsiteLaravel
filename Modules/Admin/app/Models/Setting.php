<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\PlanFactory;
use  App\Models\Media;

class Setting extends Model
{
    use HasFactory;

    protected $table='settings';
    protected $fillable=['value_mobile','value_desktop','type'];

    public function mobileMedia()
    {
        return $this->belongsTo(Media::class, 'value_mobile', 'id');
    }

    // Define the relationship for the desktop image
    public function desktopMedia()
    {
        return $this->belongsTo(Media::class, 'value_desktop', 'id');
    }
}
