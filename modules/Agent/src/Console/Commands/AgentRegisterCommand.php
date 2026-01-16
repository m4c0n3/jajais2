<?php

namespace Modules\Agent\Console\Commands;

use Illuminate\Console\Command;
use Modules\Agent\Services\AgentService;

class AgentRegisterCommand extends Command
{
    protected $signature = 'agent:register';
    protected $description = 'Register this instance with the control plane';

    public function handle(AgentService $agentService): int
    {
        try {
            $result = $agentService->register();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            $this->logAudit('agent.register', ['error' => $exception->getMessage()]);

            return self::FAILURE;
        }

        if (!$result['ok']) {
            $this->error($result['message'] ?? 'Register failed.');
            $this->logAudit('agent.register', ['error' => $result['message'] ?? 'register failed']);

            return self::FAILURE;
        }

        $this->info('Agent registered. Instance UUID: '.$result['instance_uuid']);
        $this->logAudit('agent.register', ['instance_uuid' => $result['instance_uuid']]);

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
