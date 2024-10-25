<?php

namespace Modules\Escort\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseSubscription;
use Modules\Auth\Entities\User;
use App\Enums\InqueryFormSubject;
use Modules\Auth\app\Models\AuthUser;



// use Modules\Escort\Database\Factories\OrdersFactory;

class Inquiry extends Model
{
    use HasFactory;
    protected $table = 'inquiries';


    function escort() {
      return $this->belongsTo(AuthUser::class, 'escort_id', 'id');
     }


    
}
 