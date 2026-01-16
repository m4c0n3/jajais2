<?php

namespace App\Support\Modules;

use App\Support\Licensing\LicenseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleBootManager
{
    public function __construct(private ModuleDiscovery $discovery)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function compileRegistry(): array
    {
        if (!Schema::hasTable('modules')) {
            return [];
        }

        $manifests = $this->discovery->discover();
        $states = DB::table('modules')->get()->keyBy('id');
        $registry = [];

        foreach ($manifests as $manifest) {
            $state = $states->get($manifest->id);

            $registry[] = [
                'id' => $manifest->id,
                'name' => $manifest->name,
                'version' => $manifest->version,
                'provider' => $manifest->provider,
                'enabled' => $state ? (bool) $state->enabled : false,
                'license_required' => $state ? (bool) $state->license_required : $manifest->licenseRequired,
                'requires_core' => $state?->requires_core,
            ];
        }

        return $registry;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRegistry(): array
    {
        if (!Schema::hasTable('modules')) {
            return [];
        }

        $cacheKey = (string) config('modules.cache_key', 'modules.registry');

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return is_array($cached) ? $cached : [];
        }

        return $this->compileRegistry();
    }

    /**
     * @return array<int, string>
     */
    public function bootActiveModules(): array
    {
        $registry = $this->getRegistry();
        $registered = [];

        foreach ($registry as $module) {
            $active = $this->isActive($module);

            if (!$active) {
                continue;
            }

            app()->register($module['provider']);
            $registered[] = $module['id'];
        }

        if ($registered !== []) {
            logger()->info('Modules booted', ['modules' => $registered]);
        }

        return $registered;
    }

    public function clearCache(): void
    {
        Cache::forget((string) config('modules.cache_key', 'modules.registry'));
    }

    private function isActive(array $module): bool
    {
        if (empty($module['provider']) || !is_string($module['provider'])) {
            logger()->info('Module skipped: missing provider', ['module' => $module['id']]);

            return false;
        }

        if (!class_exists($module['provider'])) {
            logger()->info('Module skipped: provider missing', ['module' => $module['id']]);

            return false;
        }

        if (empty($module['enabled'])) {
            logger()->info('Module skipped: disabled', ['module' => $module['id']]);

            return false;
        }

        if (!empty($module['license_required'])) {
            if (!class_exists(LicenseService::class)) {
                logger()->info('Module skipped: license service missing', ['module' => $module['id']]);

                return false;
            }

            if (!app(LicenseService::class)->isModuleEntitled($module['id'])) {
                logger()->info('Module skipped: license missing', ['module' => $module['id']]);

                return false;
            }
        }

        return true;
    }
}
