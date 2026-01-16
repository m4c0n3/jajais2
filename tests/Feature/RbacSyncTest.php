<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RbacSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_sync_creates_permissions_from_manifest(): void
    {
        $this->seedHelloWorldModule();

        $this->artisan('rbac:sync')->assertExitCode(0);

        $this->assertTrue(Permission::where('name', 'helloworld.view')->exists());
    }

    public function test_rbac_sync_writes_audit_log(): void
    {
        $this->seedHelloWorldModule();

        $this->artisan('rbac:sync')->assertExitCode(0);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rbac.sync',
        ]);
    }

    private function seedHelloWorldModule(): void
    {
        DB::table('modules')->insert([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'enabled' => true,
            'installed_version' => '1.0.0',
            'requires_core' => null,
            'license_required' => false,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);
    }
}
