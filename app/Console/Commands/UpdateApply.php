<?php

namespace App\Console\Commands;

use App\Support\Audit\AuditService;
use App\Support\Updates\UpdateService;
use App\Support\Webhooks\WebhookDispatcher;
use Illuminate\Console\Command;

class UpdateApply extends Command
{
    protected $signature = 'update:apply {--id=}';
    protected $description = 'Apply a pending update.';

    public function handle(UpdateService $updateService, WebhookDispatcher $dispatcher): int
    {
        $id = $this->option('id');

        if (!is_string($id) || $id === '') {
            $this->error('Update id is required.');

            return Command::FAILURE;
        }

        $update = $updateService->findPendingUpdate($id);

        if (!$update) {
            $this->error('Update not found in pending list.');

            return Command::FAILURE;
        }

        try {
            $updateService->applyUpdate($update);
            $updateService->removePendingUpdate($id);
        } catch (\Throwable $error) {
            $dispatcher->dispatch('update.failed', $this->updatePayload($update, $error->getMessage()));
            $this->logAudit('update.failed', ['id' => $id, 'error' => $error->getMessage()]);
            $this->error($error->getMessage());

            return Command::FAILURE;
        }

        $dispatcher->dispatch('update.applied', $this->updatePayload($update));
        $this->logAudit('update.applied', ['id' => $id]);
        $this->info('Update applied: '.$id);

        return Command::SUCCESS;
    }

    private function updatePayload(array $update, ?string $error = null): array
    {
        $payload = [
            'id' => $update['id'] ?? null,
            'type' => $update['type'] ?? null,
            'version' => $update['version'] ?? null,
            'channel' => $update['channel'] ?? null,
            'module_id' => $update['module_id'] ?? null,
        ];

        if ($error) {
            $payload['error'] = $error;
        }

        return $payload;
    }

    private function logAudit(string $action, array $context): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action, $context);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
