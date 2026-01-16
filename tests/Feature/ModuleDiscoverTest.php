<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleDiscovery;
use App\Support\Modules\ModuleRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ModuleDiscoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_keeps_enabled_state(): void
    {
        $path = $this->makeTempModulePath();
        config()->set('modules.path', $path);

        DB::table('modules')->insert([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'enabled' => true,
            'installed_version' => '0.9.0',
            'requires_core' => null,
            'license_required' => false,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $discovery = new ModuleDiscovery();
        $repository = new ModuleRepository();
        $repository->sync($discovery->discover());

        $this->assertTrue((bool) DB::table('modules')->where('id', 'HelloWorld')->value('enabled'));
    }

    public function test_new_module_defaults_to_disabled(): void
    {
        $path = $this->makeTempModulePath();
        config()->set('modules.path', $path);

        $discovery = new ModuleDiscovery();
        $repository = new ModuleRepository();
        $repository->sync($discovery->discover());

        $this->assertFalse((bool) DB::table('modules')->where('id', 'HelloWorld')->value('enabled'));
    }

    private function makeTempModulePath(): string
    {
        $base = sys_get_temp_dir().'/modules-'.uniqid();
        File::ensureDirectoryExists($base.'/HelloWorld');

        File::put($base.'/HelloWorld/module.json', json_encode([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'version' => '1.0.0',
            'license_required' => false,
        ], JSON_PRETTY_PRINT));

        return $base;
    }
}
