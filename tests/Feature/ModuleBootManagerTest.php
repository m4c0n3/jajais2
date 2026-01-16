<?php

namespace Tests\Feature;

use App\Support\Modules\ModuleBootManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleBootManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(config('modules.cache_key', 'modules.registry'));
    }

    public function test_disabled_module_is_not_booted(): void
    {
        $this->seedModule('HelloWorld', false, false);

        $registered = app(ModuleBootManager::class)->bootActiveModules();

        $this->assertNotContains('HelloWorld', $registered);
    }

    public function test_enabled_module_registers_routes(): void
    {
        $this->seedModule('HelloWorld', true, false);

        $registered = app(ModuleBootManager::class)->bootActiveModules();

        $this->assertContains('HelloWorld', $registered);
        $this->assertTrue($this->routeExists('hello'));
    }

    public function test_licensed_module_without_entitlement_is_not_booted(): void
    {
        $this->seedModule('HelloWorld', true, true);

        $registered = app(ModuleBootManager::class)->bootActiveModules();

        $this->assertNotContains('HelloWorld', $registered);
    }

    private function seedModule(string $id, bool $enabled, bool $licenseRequired): void
    {
        DB::table('modules')->insert([
            'id' => $id,
            'name' => $id,
            'enabled' => $enabled,
            'installed_version' => '1.0.0',
            'requires_core' => null,
            'license_required' => $licenseRequired,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);
    }

    private function routeExists(string $uri): bool
    {
        $routes = $this->app['router']->getRoutes();

        foreach ($routes as $route) {
            if ($route->uri() === $uri) {
                return true;
            }
        }

        return false;
    }
}
