<?php

namespace App\Support\Modules;

use Illuminate\Support\Facades\Schema;

class ModuleMigrationManager
{
    public function __construct(private ModuleBootManager $bootManager)
    {
    }

    public function registerEnabledMigrations(): void
    {
        if (!Schema::hasTable('modules')) {
            return;
        }

        $registry = $this->bootManager->getRegistry();
        $migrator = app('migrator');

        foreach ($registry as $module) {
            if (empty($module['enabled'])) {
                continue;
            }

            $id = $module['id'] ?? null;

            if (!is_string($id) || $id === '') {
                continue;
            }

            $path = base_path('modules/'.$id.'/database/migrations');

            if (is_dir($path)) {
                $migrator->path($path);
            }
        }
    }
}
