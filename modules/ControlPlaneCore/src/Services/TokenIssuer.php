<?php

namespace Modules\ControlPlaneCore\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;
use Modules\ControlPlaneCore\Models\SigningKey;

class TokenIssuer
{
    public function issue(array $payload): string
    {
        $key = SigningKey::where('active', true)->first();

        if (!$key) {
            throw new \RuntimeException('No signing key available');
        }

        $privateKey = Crypt::decryptString($key->private_key_encrypted);

        return JWT::encode($payload, $privateKey, 'RS256', $key->kid);
    }
}
