<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseSubscription;
use App\Models\User;

class EscortSubscription extends BaseSubscription
{

    protected $hidden = ['created_at', 'updated_at','password'];

    public function escort()
    {
        return $this->belongsTo(User::class, 'escort_id', 'id');
    }
}