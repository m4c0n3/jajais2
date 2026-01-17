<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Support\Audit\AuditService;
use App\Support\Webhooks\WebhookDispatcher;

class ModuleEnable extends Command
{
    protected $signature = 'module:enable {id}';
    protected $description = 'Enable a module by id';

    public function handle(ModuleBootManager $bootManager, WebhookDispatcher $dispatcher): int
    {
        $id = (string) $this->argument('id');

        $module = DB::table('modules')->where('id', $id)->first();

        if (!$module) {
            $this->error('Module not found. Run module:discover first.');

            return self::FAILURE;
        }

        DB::table('modules')->where('id', $id)->update(['enabled' => true]);
        $bootManager->clearCache();

        $this->info("Module enabled: {$id}");
        $this->logAudit('module.enable', $id);
        $dispatcher->dispatch('module.enabled', ['id' => $id]);

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
