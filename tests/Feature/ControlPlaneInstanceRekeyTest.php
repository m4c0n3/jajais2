<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleBootManager;
use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\ControlPlaneCore\Models\Instance;
use Tests\TestCase;

class ControlPlaneInstanceRekeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekey_updates_instance_secret(): void
    {
        $this->enableControlPlaneCore();

        $instance = Instance::create([
            'uuid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'name' => 'Test Instance',
            'api_key_hash' => Hash::make('old-secret'),
            'metadata' => ['region' => 'eu'],
            'status' => 'active',
        ]);

        $oldHash = $instance->api_key_hash;

        $this->artisan('control-plane:instance:rekey', ['uuid' => $instance->uuid])
            ->expectsOutputToContain('New instance secret:')
            ->assertExitCode(0);

        $instance->refresh();
        $this->assertNotSame($oldHash, $instance->api_key_hash);
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
