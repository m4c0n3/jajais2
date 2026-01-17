<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleBootManager;
use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ControlPlaneRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_instance(): void
    {
        $this->enableControlPlaneCore();

        $payload = [
            'instance_uuid' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Test Instance',
            'metadata' => ['region' => 'eu'],
        ];

        $response = $this->postJson('/api/v1/instances/register', $payload);

        $response->assertOk();
        $response->assertJsonStructure([
            'instance_uuid',
            'instance_secret',
            'registered_at',
        ]);

        $this->assertDatabaseHas('cp_instances', [
            'uuid' => $payload['instance_uuid'],
        ]);
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
