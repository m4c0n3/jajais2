<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGrantCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_grant_assigns_role_and_permission(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->artisan('admin:grant', [
            'email' => $user->email,
            '--role' => 'super-admin',
            '--permission' => 'admin.access',
        ])->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('super-admin'));
        $this->assertTrue($user->fresh()->hasPermissionTo('admin.access'));
    }
}
