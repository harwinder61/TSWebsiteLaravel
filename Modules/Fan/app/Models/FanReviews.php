<?php

namespace Modules\Fan\app\Models;

use App\Models\BaseReviews;
use Illuminate\Database\Eloquent\Model;
use Modules\Users\Entities\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FanReviews extends BaseReviews
{

    public function escort(){
        
        return $this->belongsTo(User::class,'escort_id','id');
    }


}
