<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Support\Audit\AuditService;
use App\Support\Webhooks\WebhookDispatcher;

class ModuleDisable extends Command
{
    protected $signature = 'module:disable {id}';
    protected $description = 'Disable a module by id';

    public function handle(ModuleBootManager $bootManager, WebhookDispatcher $dispatcher): int
    {
        $id = (string) $this->argument('id');

        $module = DB::table('modules')->where('id', $id)->first();

        if (!$module) {
            $this->error('Module not found. Run module:discover first.');

            return self::FAILURE;
        }

        DB::table('modules')->where('id', $id)->update(['enabled' => false]);
        $bootManager->clearCache();

        $this->info("Module disabled: {$id}");
        $this->logAudit('module.disable', $id);
        $dispatcher->dispatch('module.disabled', ['id' => $id]);

        return self::SUCCESS;
    }

    private function logAudit(string $action, string $moduleId): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action, [
                'target_type' => 'module',
                'target_id' => $moduleId,
            ]);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
