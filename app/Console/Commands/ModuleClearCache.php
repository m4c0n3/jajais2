<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ModuleClearCache extends Command
{
    protected $signature = 'module:clear-cache';
    protected $description = 'Clear module registry cache';

    public function handle(): int
    {
        $cacheKey = (string) config('modules.cache_key', 'modules.registry');
        Cache::forget($cacheKey);

        $this->info('Module registry cache cleared.');

        return self::SUCCESS;
    }
}
