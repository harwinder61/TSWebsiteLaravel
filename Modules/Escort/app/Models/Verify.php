<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Escort\app\Models\Escort;
use App\Models\User;
use Modules\Fan\app\Models\Fan;

class Verify extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "verify";

    /**
     * The attributes that are mass assignable.
     * I have added 'escort_id' and the new 'didit_' fields here.
     */
    protected $fillable = [
        'escort_id',            // <--- CRITICAL FIX for your error
        'fan_id',
        'passport_image',
        'selfie_image',
        'verified_status',
        'action',
        
        // Didit Integration Fields
        'didit_session_id',
        'didit_workflow_id',
        'didit_session_token',
        'didit_status',
        'didit_completed_at',
        
        // Admin Manual Review Fields
        'admin_notes',
        'admin_reviewed_at'
    ];

    public function escort()
    {
        return $this->belongsTo(Escort::class, 'escort_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'escort_id', 'id');
    }

    public function fan()
    {
        return $this->belongsTo(User::class, 'fan_id', 'id');
    }

    public function profile()
    {
        // specific to your structure: Verify shares the same User ID as Profile
        return $this->belongsTo(Profile::class, 'escort_id', 'escort_id'); 
    }
}