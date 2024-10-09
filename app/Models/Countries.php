<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Countries extends Model
{
    protected $table = 'locations_countries';
    protected $fillable = ['name'];
}