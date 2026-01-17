<?php

namespace Modules\ControlPlaneCore\Models;

use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    protected $table = 'cp_entitlements';

    protected $fillable = [
        'instance_id',
        'module_id',
        'valid_to',
        'grace_to',
        'enabled',
    ];

    protected $casts = [
        'valid_to' => 'datetime',
        'grace_to' => 'datetime',
        'enabled' => 'boolean',
    ];
}
