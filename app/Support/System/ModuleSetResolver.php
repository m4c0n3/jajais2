<?php

namespace App\Support\System;

class ModuleSetResolver
{
    /**
     * @return array<int, string>
     */
    public function resolve(string $mode): array
    {
        $set = config('module_sets.'.$mode, []);

        return is_array($set) ? $set : [];
    }

    public function modeFromEnv(): ?string
    {
        $mode = env('APP_MODE');

        if (!is_string($mode) || $mode === '') {
            return null;
        }

        return $mode;
    }
}
