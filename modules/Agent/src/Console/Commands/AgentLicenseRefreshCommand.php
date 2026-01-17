<?php

namespace Modules\Agent\Console\Commands;

use Illuminate\Console\Command;
use Modules\Agent\Services\AgentService;

class AgentLicenseRefreshCommand extends Command
{
    protected $signature = 'agent:license-refresh';
    protected $description = 'Refresh license token from the control plane';

    public function handle(AgentService $agentService): int
    {
        try {
            $result = $agentService->refreshLicenseToken();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            $this->logAudit('agent.license_refresh_failed', ['error' => $exception->getMessage()]);

            return self::FAILURE;
        }

        if (!$result['ok']) {
            $this->error($result['message'] ?? 'License refresh failed.');
            $this->logAudit('agent.license_refresh_failed', ['error' => $result['message'] ?? 'license refresh failed']);

            return self::FAILURE;
        }

        $this->info('License token refreshed.');
        $this->logAudit('agent.license_refresh');

        return self::SUCCESS;
    }

    private function logAudit(string $action, array $context = []): void
    {
        if (!class_exists('App\\Support\\Audit\\AuditService')) {
            return;
        }

        try {
            app('App\\Support\\Audit\\AuditService')->log($action, $context);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
