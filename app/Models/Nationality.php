<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Nationality extends Model
{
    protected $table = 'locations_nationalities';
    protected $fillable = ['name'];
}