<?php

namespace App\Console\Commands;

use App\Support\Audit\AuditService;
use App\Support\Ops\SystemStatusService;
use Illuminate\Console\Command;

class SystemStatus extends Command
{
    protected $signature = 'system:status';
    protected $description = 'Show system status summary';

    public function handle(SystemStatusService $statusService): int
    {
        $summary = $statusService->summary();
        $dbOk = $summary['db']['ok'] ?? false;
        $dbError = $summary['db']['error'] ?? null;
        $queueDriver = $summary['queue_driver'] ?? null;
        $lastHeartbeat = $summary['agent']['last_heartbeat_at'] ?? null;
        $lastLicenseRefresh = $summary['agent']['last_license_refresh_at'] ?? null;
        $failedWebhooks = $summary['webhooks']['failed_deliveries'] ?? null;
        $licenseSummary = $this->formatLicenseSummary($summary['license'] ?? []);

        $this->info('System status');
        $this->line('DB: ' . ($dbOk ? 'ok' : 'fail'));
        if (!$dbOk && $dbError !== null) {
            $this->line('DB error: ' . $dbError);
        }
        $this->line('Queue driver: ' . ($queueDriver ?? 'unknown'));
        $this->line('Last heartbeat: ' . ($lastHeartbeat ?? 'n/a'));
        $this->line('Last license refresh: ' . ($lastLicenseRefresh ?? 'n/a'));
        $this->line('Webhook failed deliveries: ' . ($failedWebhooks === null ? 'n/a' : (string) $failedWebhooks));
        $this->line('License: ' . $licenseSummary);

        $this->auditStatus($dbOk, $queueDriver);

        return $dbOk ? Command::SUCCESS : Command::FAILURE;
    }

    private function formatLicenseSummary(array $license): string
    {
        $status = $license['status'] ?? 'n/a';
        $validTo = $license['valid_to'] ?? null;
        $graceTo = $license['grace_to'] ?? null;

        if ($status === 'valid') {
            return 'valid (until ' . ($validTo ?? 'unknown') . ')';
        }

        if ($status === 'grace') {
            return 'grace (until ' . ($graceTo ?? 'unknown') . ')';
        }

        return $status;
    }

    private function auditStatus(bool $dbOk, ?string $queueDriver): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log('ops.system_status', [
                'db_ok' => $dbOk,
                'queue_driver' => $queueDriver,
            ]);
        } catch (\Throwable) {
            // Best-effort audit logging only.
        }
    }
}
