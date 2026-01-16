<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ModuleCache extends Command
{
    protected $signature = 'module:cache';
    protected $description = 'Cache module registry';

    public function handle(ModuleBootManager $bootManager): int
    {
        $registry = $bootManager->compileRegistry();
        $cacheKey = (string) config('modules.cache_key', 'modules.registry');

        Cache::forever($cacheKey, $registry);

        $this->info('Module registry cached: '.count($registry).' modules.');

        return self::SUCCESS;
    }
}
