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
    protected $fillable=['value','type'];

    public function media(){
        return $this->belongsTo(Media::class, 'value', 'id');
    }

}
