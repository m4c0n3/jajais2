<?php

namespace App\Console\Commands;

use App\Support\Ops\SystemStatusService;
use App\Support\System\AppMode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CloudronDiag extends Command
{
    protected $signature = 'cloudron:diag';
    protected $description = 'Show Cloudron diagnostic summary (no secrets).';

    public function handle(SystemStatusService $statusService, AppMode $appMode): int
    {
        $summary = $statusService->summary();

        $this->info('Cloudron diagnostics');
        $this->line('App mode: '.($appMode->current() ?? 'unknown'));
        $this->line('DB driver: '.config('database.default'));
        $this->line('Cache store: '.config('cache.default'));
        $this->line('Queue driver: '.config('queue.default'));
        $this->line('Storage path: '.storage_path());
        $this->line('Storage writable: '.(is_writable(storage_path()) ? 'yes' : 'no'));

        $dbOk = $summary['db']['ok'] ?? false;
        $this->line('DB ok: '.($dbOk ? 'yes' : 'no'));

        $agent = $summary['agent'] ?? [];
        $this->line('Last heartbeat: '.($agent['last_heartbeat_at'] ?? 'n/a'));
        $this->line('Last license refresh: '.($agent['last_license_refresh_at'] ?? 'n/a'));

        $webhooks = $summary['webhooks'] ?? [];
        $failedDeliveries = $webhooks['failed_deliveries'] ?? null;
        $this->line('Failed webhooks: '.($failedDeliveries === null ? 'n/a' : (string) $failedDeliveries));

        $license = $summary['license'] ?? [];
        $this->line('License status: '.($license['status'] ?? 'n/a'));

        if (!Schema::hasTable('system_settings')) {
            $this->warn('system_settings table missing.');
        }

        return $dbOk ? Command::SUCCESS : Command::FAILURE;
    }
}
