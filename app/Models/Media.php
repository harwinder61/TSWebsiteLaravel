<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Media extends Model
{
    protected $table = 'media';
    protected $fillable = ['type','path','is_temp'];

   
}