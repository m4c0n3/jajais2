<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleBootManager;
use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Modules\ControlPlaneCore\Models\SigningKey;
use Tests\TestCase;

class ControlPlaneKeyRevokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoke_marks_key_inactive(): void
    {
        $this->enableControlPlaneCore();

        SigningKey::create([
            'kid' => 'revoked-kid',
            'public_key' => 'test-public',
            'private_key_encrypted' => Crypt::encryptString('test-private'),
            'active' => true,
        ]);

        $this->artisan('control-plane:key:revoke', ['kid' => 'revoked-kid'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('cp_signing_keys', [
            'kid' => 'revoked-kid',
            'active' => false,
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
