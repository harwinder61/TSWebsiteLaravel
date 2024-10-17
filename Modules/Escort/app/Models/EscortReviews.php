<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseReviews;
use Modules\Auth\app\Models\AuthUser;    
// use Modules\Escort\Database\Factories\EscortFactory;

class EscortReviews extends BaseReviews
{

    public function fan()
    {
        return $this->belongsTo(AuthUser::class, 'user_id', 'id');
    }

    
}
