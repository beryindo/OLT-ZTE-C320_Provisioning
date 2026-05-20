<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    protected $fillable = [
        'name', 'ip', 'telnet_username', 'telnet_password', 'telnet_port',
        'snmp_username', 'snmp_password', 'snmp_port'
    ];
}
