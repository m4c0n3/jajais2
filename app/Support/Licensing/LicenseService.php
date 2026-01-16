<?php

namespace App\Support\Licensing;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    private string $cacheKey = 'license.active';
    private int $cacheSeconds = 300;

    public function isLicenseValid(): bool
    {
        $token = $this->getActiveToken();

        if (!$token || !$token['valid_to']) {
            return false;
        }

        return CarbonImmutable::now()->lte($token['valid_to']);
    }

    public function isInGrace(): bool
    {
        $token = $this->getActiveToken();

        if (!$token || !$token['valid_to'] || !$token['grace_to']) {
            return false;
        }

        $now = CarbonImmutable::now();

        return $now->gt($token['valid_to']) && $now->lte($token['grace_to']);
    }

    public function isModuleEntitled(string $moduleId): bool
    {
        $token = $this->getActiveToken();

        if (!$token || !$this->isLicenseEffective($token)) {
            return false;
        }

        $modules = $token['parsed']['modules'] ?? [];

        if (!is_array($modules)) {
            return false;
        }

        return in_array($moduleId, $modules, true);
    }

    public function getMeta(): array
    {
        $token = $this->getActiveToken();

        return [
            'valid_to' => $token['valid_to'] ?? null,
            'grace_to' => $token['grace_to'] ?? null,
        ];
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function parseToken(string $token): ?array
    {
        // TODO: Add signature verification in a future CP before trusting token claims.
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $payload = $this->parseJwtPayload($token);

        if ($payload !== null) {
            return $payload;
        }

        if (str_starts_with($token, '{') || str_starts_with($token, '[')) {
            $decoded = json_decode($token, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    public function extractDates(?array $parsed): array
    {
        if (!is_array($parsed)) {
            return [null, null];
        }

        $validTo = $this->parseDateValue($parsed['valid_to'] ?? $parsed['exp'] ?? null);
        $graceTo = $this->parseDateValue($parsed['grace_to'] ?? null);

        return [$validTo, $graceTo];
    }

    private function getActiveToken(): ?array
    {
        return Cache::remember($this->cacheKey, $this->cacheSeconds, function (): ?array {
            $record = DB::table('license_tokens')
                ->whereNull('revoked_at')
                ->orderByDesc('created_at')
                ->first();

            if (!$record) {
                return null;
            }

            $parsed = $record->parsed ? json_decode($record->parsed, true) : null;
            $parsed = is_array($parsed) ? $parsed : $this->parseToken($record->token);

            return [
                'id' => $record->id,
                'token' => $record->token,
                'valid_to' => $record->valid_to ? CarbonImmutable::parse($record->valid_to) : null,
                'grace_to' => $record->grace_to ? CarbonImmutable::parse($record->grace_to) : null,
                'parsed' => $parsed ?? [],
            ];
        });
    }

    private function isLicenseEffective(array $token): bool
    {
        if (!$token['valid_to']) {
            return false;
        }

        $now = CarbonImmutable::now();

        if ($now->lte($token['valid_to'])) {
            return true;
        }

        return $token['grace_to'] && $now->lte($token['grace_to']);
    }

    private function parseJwtPayload(string $token): ?array
    {
        if (!str_contains($token, '.')) {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) < 2) {
            return null;
        }

        $payload = $this->base64UrlDecode($parts[1]);

        if ($payload === null) {
            return null;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function base64UrlDecode(string $data): ?string
    {
        $data = strtr($data, '-_', '+/');
        $padding = strlen($data) % 4;

        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($data, true);

        return $decoded === false ? null : $decoded;
    }

    private function parseDateValue(mixed $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return CarbonImmutable::createFromTimestamp((int) $value);
        }

        if (is_string($value) && $value !== '') {
            try {
                return CarbonImmutable::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
