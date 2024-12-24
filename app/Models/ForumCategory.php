<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ForumCategory extends Model
{


    protected $table = 'forum_categories';
    protected $fillable = ['name','description'];
}