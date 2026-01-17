<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleBootManager;
use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\ControlPlaneCore\Models\Instance;
use Tests\TestCase;

class ControlPlaneHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_heartbeat_requires_valid_secret(): void
    {
        $this->enableControlPlaneCore();

        $instance = Instance::create([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'name' => 'Test',
            'api_key_hash' => Hash::make('secret'),
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/instances/heartbeat', [
            'instance_uuid' => $instance->uuid,
            'instance_secret' => 'bad-secret',
        ])->assertStatus(401);

        $this->postJson('/api/v1/instances/heartbeat', [
            'instance_uuid' => $instance->uuid,
            'instance_secret' => 'secret',
        ])->assertOk();
    }

    private function enableControlPlaneCore(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 'ControlPlaneCore'],
            [
                'name' => 'ControlPlaneCore',
                'enabled' => true,
                'installed_version' => '1.0.0',
                'requires_core' => null,
                'license_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $bootManager = app(ModuleBootManager::class);
        $bootManager->clearCache();

        app(ModuleMigrationManager::class)->registerEnabledMigrations();
        $bootManager->bootActiveModules();

        $migrator = app('migrator');
        $paths = array_merge([database_path('migrations')], $migrator->paths());
        $migrator->run($paths);
    }
}
