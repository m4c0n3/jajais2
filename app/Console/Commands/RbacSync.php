<?php

namespace App\Console\Commands;

use App\Support\Audit\AuditService;
use App\Support\Modules\ModuleBootManager;
use App\Support\Webhooks\WebhookDispatcher;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSync extends Command
{
    protected $signature = 'rbac:sync';
    protected $description = 'Sync RBAC permissions from active module manifests';

    public function handle(ModuleBootManager $bootManager, WebhookDispatcher $dispatcher): int
    {
        $guard = (string) config('auth.defaults.guard', 'web');
        $activeModules = $bootManager->getActiveModules();

        $created = 0;
        $permissions = [];
        $corePermissions = ['admin.access', 'users.manage'];

        foreach ($corePermissions as $permission) {
            $permissions[] = $permission;

            if (!Permission::where('name', $permission)->where('guard_name', $guard)->exists()) {
                Permission::create(['name' => $permission, 'guard_name' => $guard]);
                $created++;
            }
        }

        foreach ($activeModules as $module) {
            foreach ($module['permissions'] ?? [] as $permission) {
                if (!is_string($permission) || $permission === '') {
                    continue;
                }

                $permissions[] = $permission;

                if (!Permission::where('name', $permission)->where('guard_name', $guard)->exists()) {
                    Permission::create(['name' => $permission, 'guard_name' => $guard]);
                    $created++;
                }
            }
        }

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);

        $this->info("Permissions created: {$created}");
        $this->logAudit('rbac.sync', [
            'created' => $created,
            'permissions' => $permissions,
        ]);
        $dispatcher->dispatch('rbac.synced', ['created' => $created]);

        return self::SUCCESS;
    }

    private function logAudit(string $action, array $metadata = []): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action, [
                'metadata' => $metadata,
            ]);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
