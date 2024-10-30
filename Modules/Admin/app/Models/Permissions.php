<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Admin\app\Models\Permission;

class Permissions extends Model
{
    protected $table = 'permissions';
    protected $fillable = ['title'];

}
