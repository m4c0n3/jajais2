<?php

namespace App\Console\Commands;

use App\Support\Audit\AuditService;
use App\Support\Updates\UpdateService;
use App\Support\Webhooks\WebhookDispatcher;
use Illuminate\Console\Command;

class UpdateCheck extends Command
{
    protected $signature = 'update:check {--manifest=} {--url=}';
    protected $description = 'Check for available updates.';

    public function handle(UpdateService $updateService, WebhookDispatcher $dispatcher): int
    {
        try {
            $updates = $this->loadUpdates($updateService);
        } catch (\Throwable $error) {
            $this->error($error->getMessage());
            $this->logAudit('update.failed', ['error' => $error->getMessage()]);

            return Command::FAILURE;
        }

        $channel = (string) config('updates.channel', 'stable');
        $updates = $updateService->filterByChannel($updates, $channel);

        if ($updates === []) {
            $this->info('No updates available.');
            $updateService->storePending([]);

            return Command::SUCCESS;
        }

        $updateService->storePending($updates);

        foreach ($updates as $update) {
            $dispatcher->dispatch('update.available', $this->updatePayload($update));
        }

        $this->logAudit('update.available', ['count' => count($updates), 'channel' => $channel]);

        $this->info('Updates available: '.count($updates));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadUpdates(UpdateService $updateService): array
    {
        $manifest = $this->option('manifest');
        $url = $this->option('url');

        if (is_string($manifest) && $manifest !== '') {
            return $updateService->loadManifestFromPath($manifest);
        }

        if (is_string($url) && $url !== '') {
            return $updateService->loadManifestFromUrl($url);
        }

        $configUrl = config('updates.manifest_url');
        if (is_string($configUrl) && $configUrl !== '') {
            return $updateService->loadManifestFromUrl($configUrl);
        }

        $configPath = config('updates.manifest_path');
        if (is_string($configPath) && $configPath !== '') {
            return $updateService->loadManifestFromPath($configPath);
        }

        throw new \RuntimeException('No update manifest source configured.');
    }

    private function updatePayload(array $update): array
    {
        return [
            'id' => $update['id'] ?? null,
            'type' => $update['type'] ?? null,
            'version' => $update['version'] ?? null,
            'channel' => $update['channel'] ?? null,
            'module_id' => $update['module_id'] ?? null,
        ];
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
