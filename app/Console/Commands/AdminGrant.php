<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminGrant extends Command
{
    protected $signature = 'admin:grant {email} {--role=super-admin} {--permission=admin.access}';
    protected $description = 'Grant admin access role/permission to a user.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $roleName = (string) $this->option('role');
        $permissionName = (string) $this->option('permission');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found.');

            return Command::FAILURE;
        }

        $guard = (string) config('auth.defaults.guard', 'web');

        if ($roleName !== '') {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $user->assignRole($role);
        }

        if ($permissionName !== '') {
            $permission = Permission::findOrCreate($permissionName, $guard);
            $user->givePermissionTo($permission);
        }

        $this->info('Admin access granted.');

        return Command::SUCCESS;
    }
}
