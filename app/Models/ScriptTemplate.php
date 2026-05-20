<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptTemplate extends Model
{
    protected $fillable = ['merk', 'gpon_onu_script', 'pon_onu_mng_script', 'is_default'];
}
