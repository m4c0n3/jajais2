<?php

namespace App\Support\Licensing;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\File;

class JwtTokenVerifier
{
    /**
     * @return array<string, mixed>
     */
    public function verify(string $jwt, ?string $expectedAudience = null): array
    {
        $publicKey = $this->loadPublicKey();
        $issuer = $this->requireConfig('agent.jwt_issuer');
        $audience = $expectedAudience ?? $this->requireConfig('agent.jwt_audience');

        $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
        $claims = json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIssuer($claims, $issuer);
        $this->assertAudience($claims, $audience);

        return $claims;
    }

    private function loadPublicKey(): string
    {
        $inline = config('agent.jwt_public_key');

        if (is_string($inline) && $inline !== '') {
            return $inline;
        }

        $path = config('agent.jwt_public_key_path');

        if (is_string($path) && $path !== '') {
            if (!File::exists($path)) {
                throw new \RuntimeException('JWT public key file not found.');
            }

            return File::get($path);
        }

        throw new \RuntimeException('JWT public key is not configured.');
    }

    private function requireConfig(string $key): string
    {
        $value = config($key);

        if (!is_string($value) || $value === '') {
            throw new \RuntimeException("Missing required config: {$key}");
        }

        return $value;
    }

    private function assertIssuer(array $claims, string $issuer): void
    {
        if (($claims['iss'] ?? null) !== $issuer) {
            throw new \RuntimeException('JWT issuer mismatch.');
        }
    }

    private function assertAudience(array $claims, string $audience): void
    {
        $claim = $claims['aud'] ?? null;

        if (is_array($claim)) {
            if (!in_array($audience, $claim, true)) {
                throw new \RuntimeException('JWT audience mismatch.');
            }

            return;
        }

        if ($claim !== $audience) {
            throw new \RuntimeException('JWT audience mismatch.');
        }
    }
}
