<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Cities extends Model
{
    protected $table = 'locations_cities';
    protected $fillable = ['name'];
}