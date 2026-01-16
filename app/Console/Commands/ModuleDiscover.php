<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleDiscovery;
use App\Support\Modules\ModuleRepository;
use Illuminate\Console\Command;

class ModuleDiscover extends Command
{
    protected $signature = 'module:discover';
    protected $description = 'Discover modules and sync registry table';

    public function handle(ModuleDiscovery $discovery, ModuleRepository $repository): int
    {
        try {
            $manifests = $discovery->discover();
            $results = $repository->sync($manifests);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (class_exists(\App\Support\Modules\ModuleBootManager::class)) {
            app(\App\Support\Modules\ModuleBootManager::class)->clearCache();
        }

        if ($results === []) {
            $this->info('No modules found.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Version', 'Enabled', 'License Required', 'Provider'], array_map(function (array $row): array {
            return [
                $row['id'],
                $row['version'],
                $row['enabled'] ? 'yes' : 'no',
                $row['license_required'] ? 'yes' : 'no',
                $row['provider'] ?? '-',
            ];
        }, $results));

        return self::SUCCESS;
    }
}
