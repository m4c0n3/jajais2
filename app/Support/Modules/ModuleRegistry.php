<?php

namespace App\Support\Modules;

use Illuminate\Support\Facades\DB;

class ModuleRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $modules = DB::table('modules')->get();

        return $modules->map(fn ($module) => [
            'id' => $module->id,
            'name' => $module->name,
            'installed_version' => $module->installed_version,
            'enabled' => (bool) $module->enabled,
            'requires_core' => $module->requires_core,
            'license_required' => (bool) $module->license_required,
        ])->all();
    }
}
