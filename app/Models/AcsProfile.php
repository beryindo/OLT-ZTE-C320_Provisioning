<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcsProfile extends Model
{
    protected $fillable = ['name', 'url', 'username', 'password', 'is_default'];
}
