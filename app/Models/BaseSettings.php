<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseProfile;
use Modules\Auth\app\Models\AuthUser;
class BaseSettings extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'key','value','custom_link'
    ];

    protected $casts  =  [
        'value' => 'json'
    ];

    public function media(){
        // Extract the ID from the JSON value
        // If value is a JSON object/array, get the first element or the id property
        $mediaId = null;
        
        if (is_array($this->value)) {
            // If value is an array, check if it has an 'id' key or use first element
            $mediaId = $this->value['id'] ?? (is_numeric($this->value[0] ?? null) ? $this->value[0] : null);
        } elseif (is_numeric($this->value)) {
            // If value is already numeric, use it directly
            $mediaId = $this->value;
        }
        
        if ($mediaId) {
            return $this->belongsTo(Media::class, null, 'id')->where('id', $mediaId);
        }
        
        // Return an empty relationship if no valid ID found
        return $this->belongsTo(Media::class)->whereNull('id');
    }

    

    
}


