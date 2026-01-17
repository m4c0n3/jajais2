<?php

namespace Modules\ControlPlaneCore\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $table = 'cp_instances';

    protected $fillable = [
        'uuid',
        'name',
        'api_key_hash',
        'metadata',
        'last_seen_at',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
