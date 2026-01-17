<?php

namespace App\Support\System;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemSettings
{
    public function getString(string $key): ?string
    {
        $value = $this->getRaw($key);

        return is_string($value) ? $value : null;
    }

    public function getBool(string $key): bool
    {
        $value = $this->getRaw($key);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes'], true);
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return false;
    }

    public function set(string $key, mixed $value): void
    {
        if (!Schema::hasTable('system_settings')) {
            throw new \RuntimeException('system_settings table missing. Run migrations.');
        }

        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => is_string($value) ? $value : json_encode($value),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function isInitialized(): bool
    {
        if (!Schema::hasTable('system_settings')) {
            return false;
        }

        return $this->getBool('app.locked');
    }

    public function mode(): ?string
    {
        return $this->getString('app.mode');
    }

    private function getRaw(string $key): mixed
    {
        if (!Schema::hasTable('system_settings')) {
            return null;
        }

        $row = DB::table('system_settings')->where('key', $key)->first();

        if (!$row) {
            return null;
        }

        $value = $row->value ?? null;

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
