<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Escort\app\Models\ProfileRates;
use Modules\Escort\app\Models\Profile;
use Modules\Admin\app\Models\Comment;
use Modules\Admin\app\Models\Blog;
use Modules\Admin\app\Models\Sms;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class Tslogo extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'ts_logo';
    protected $fillable = [
        'logo_path',
    ];
}