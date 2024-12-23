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

    public function media()
    {
        // Decode the 'value' field (which contains media IDs) into an array
        $mediaIds = json_decode($this->value);

        // Retrieve the Media records that match the IDs in 'value'
        return Media::whereIn('id', $mediaIds)->get();
    }
}
