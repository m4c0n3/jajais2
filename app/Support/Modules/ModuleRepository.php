<?php

namespace App\Support\Modules;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ModuleRepository
{
    /**
     * @param array<int, ModuleManifest> $manifests
     * @return array<int, array<string, mixed>>
     */
    public function sync(array $manifests): array
    {
        $now = CarbonImmutable::now();
        $results = [];

        foreach ($manifests as $manifest) {
            $existing = DB::table('modules')->where('id', $manifest->id)->first();

            if ($existing) {
                DB::table('modules')->where('id', $manifest->id)->update([
                    'name' => $manifest->name,
                    'installed_version' => $manifest->version,
                    'requires_core' => $manifest->requiresCore,
                    'license_required' => $manifest->licenseRequired,
                    'updated_at' => $now,
                ]);

                $results[] = [
                    'id' => $manifest->id,
                    'version' => $manifest->version,
                    'enabled' => (bool) $existing->enabled,
                    'license_required' => (bool) $manifest->licenseRequired,
                    'provider' => $manifest->provider,
                ];

                continue;
            }

            DB::table('modules')->insert([
                'id' => $manifest->id,
                'name' => $manifest->name,
                'enabled' => false,
                'installed_version' => $manifest->version,
                'requires_core' => $manifest->requiresCore,
                'license_required' => $manifest->licenseRequired,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $results[] = [
                'id' => $manifest->id,
                'version' => $manifest->version,
                'enabled' => false,
                'license_required' => (bool) $manifest->licenseRequired,
                'provider' => $manifest->provider,
            ];
        }

        return $results;
    }
}
