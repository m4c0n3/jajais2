<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuleMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_migrations_do_not_run(): void
    {
        DB::table('modules')->insert([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'enabled' => false,
            'installed_version' => '1.0.0',
            'requires_core' => null,
            'license_required' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ModuleMigrationManager::class)->registerEnabledMigrations();
        $this->runModuleMigrations();

        $this->assertFalse(Schema::hasTable('helloworld_samples'));
    }

    public function test_enabled_module_migrations_run(): void
    {
        DB::table('modules')->insert([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'enabled' => true,
            'installed_version' => '1.0.0',
            'requires_core' => null,
            'license_required' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ModuleMigrationManager::class)->registerEnabledMigrations();
        $this->runModuleMigrations();

        $this->assertTrue(Schema::hasTable('helloworld_samples'));
    }

    private function runModuleMigrations(): void
    {
        $migrator = app('migrator');
        $paths = array_merge([database_path('migrations')], $migrator->paths());
        $migrator->run($paths);
    }
}
