<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'name',
        'url',
        'is_active',
        'secret',
        'events',
        'headers',
        'timeout_seconds',
        'max_attempts',
        'backoff_seconds',
        'last_success_at',
        'last_failure_at',
        'last_failure_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'events' => 'array',
        'headers' => 'array',
        'backoff_seconds' => 'array',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];
}
