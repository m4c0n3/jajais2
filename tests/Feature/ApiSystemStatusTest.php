<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApiSystemStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_status_requires_authentication(): void
    {
        $this->getJson('/api/v1/system/status')->assertStatus(401);
    }

    public function test_system_status_is_rate_limited(): void
    {
        RateLimiter::for('api-token', function () {
            return Limit::perMinute(1);
        });

        $user = User::factory()->create();
        Permission::findOrCreate('system.status.view');
        $user->givePermissionTo('system.status.view');

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/system/status')->assertOk();
        $this->getJson('/api/v1/system/status')->assertStatus(429);
    }

    public function test_system_status_is_audited(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('system.status.view');
        $user->givePermissionTo('system.status.view');

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/system/status')->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'api.system_status_viewed',
        ]);
    }
}
