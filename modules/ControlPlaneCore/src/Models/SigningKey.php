<?php

namespace Modules\ControlPlaneCore\Models;

use Illuminate\Database\Eloquent\Model;

class SigningKey extends Model
{
    protected $table = 'cp_signing_keys';

    protected $fillable = [
        'kid',
        'public_key',
        'private_key_encrypted',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
