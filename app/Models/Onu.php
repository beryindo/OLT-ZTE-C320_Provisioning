<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Onu extends Model
{
    protected $fillable = [
        'olt_id', 'board', 'slot', 'port', 'onu_index', 'sn', 'name', 'type'
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }
}
