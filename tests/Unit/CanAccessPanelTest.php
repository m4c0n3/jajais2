<?php

namespace Tests\Unit;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CanAccessPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_panel(): void
    {
        $user = User::factory()->create();
        $guard = (string) config('auth.defaults.guard', 'web');
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        $user->assignRole($role);

        $this->assertTrue($user->canAccessPanel(Panel::make()));
    }

    public function test_admin_access_permission_allows_panel(): void
    {
        $user = User::factory()->create();
        $guard = (string) config('auth.defaults.guard', 'web');
        $permission = Permission::findOrCreate('admin.access', $guard);
        $user->givePermissionTo($permission);

        $this->assertTrue($user->canAccessPanel(Panel::make()));
    }

    public function test_user_without_access_is_denied(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->canAccessPanel(Panel::make()));
    }
}
