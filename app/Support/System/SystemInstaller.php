<?php

namespace App\Support\System;

use App\Models\User;
use App\Support\Modules\ModuleBootManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class SystemInstaller
{
    public function __construct(private SystemSettings $settings, private ModuleBootManager $bootManager)
    {
    }

    public function install(string $mode, string $adminName, string $adminEmail, string $adminPassword): void
    {
        if ($this->settings->isInitialized()) {
            throw new \RuntimeException('System is already initialized.');
        }

        $this->validateMode($mode);

        if (!Schema::hasTable('system_settings')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        $this->settings->set('app.mode', $mode);
        $this->settings->set('app.locked', true);
        $this->settings->set('app.initialized_at', now()->toIso8601String());

        Artisan::call('module:discover');
        $this->enableModuleSet($mode);
        $this->bootManager->clearCache();

        Artisan::call('migrate', ['--force' => true]);

        $admin = $this->ensureAdminUser($adminName, $adminEmail, $adminPassword);

        Artisan::call('rbac:sync');

        $role = Role::where('name', 'super-admin')->first();
        if ($role) {
            $admin->assignRole($role);
        }
    }

    private function validateMode(string $mode): void
    {
        if (!in_array($mode, ['client', 'control-plane'], true)) {
            throw new \InvalidArgumentException('Invalid mode. Use client or control-plane.');
        }
    }

    private function enableModuleSet(string $mode): void
    {
        $set = config('module_sets.'.$mode, []);

        if (!is_array($set) || $set === []) {
            return;
        }

        $available = DB::table('modules')
            ->whereIn('id', $set)
            ->pluck('id')
            ->all();

        if ($available === []) {
            return;
        }

        DB::table('modules')->whereIn('id', $available)->update(['enabled' => true]);
    }

    private function ensureAdminUser(string $name, string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
