<?php

namespace Modules\Agent\Console\Commands;

use Illuminate\Console\Command;
use Modules\Agent\Services\AgentService;

class AgentHeartbeatCommand extends Command
{
    protected $signature = 'agent:heartbeat';
    protected $description = 'Send heartbeat to the control plane';

    public function handle(AgentService $agentService): int
    {
        try {
            $result = $agentService->sendHeartbeat();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            $this->logAudit('agent.heartbeat_failed', ['error' => $exception->getMessage()]);

            return self::FAILURE;
        }

        if (!$result['ok']) {
            $this->error($result['message'] ?? 'Heartbeat failed.');
            $this->logAudit('agent.heartbeat_failed', ['error' => $result['message'] ?? 'heartbeat failed']);

            return self::FAILURE;
        }

        $this->info('Heartbeat sent.');
        $this->logAudit('agent.heartbeat');

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
