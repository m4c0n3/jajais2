<?php

namespace App\Support\Updates;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UpdateService
{
    public function __construct(private UpdateManifestVerifier $verifier)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function loadManifestFromPath(string $path): array
    {
        if (!File::exists($path)) {
            throw new \RuntimeException('Update manifest file not found.');
        }

        $jwt = trim(File::get($path));

        return $this->loadManifestFromJwt($jwt);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function loadManifestFromUrl(string $url): array
    {
        $response = Http::timeout(10)->retry(2, 250)->get($url);

        if (!$response->ok()) {
            throw new \RuntimeException('Failed to download update manifest.');
        }

        return $this->loadManifestFromJwt(trim($response->body()));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function loadManifestFromJwt(string $jwt): array
    {
        $claims = $this->verifier->verify($jwt);
        $updates = $claims['updates'] ?? [];

        return is_array($updates) ? array_values($updates) : [];
    }

    /**
     * @param array<int, array<string, mixed>> $updates
     * @return array<int, array<string, mixed>>
     */
    public function filterByChannel(array $updates, string $channel): array
    {
        return array_values(array_filter($updates, function (array $update) use ($channel): bool {
            $updateChannel = $update['channel'] ?? null;

            if ($updateChannel === null || $updateChannel === '') {
                return $channel === 'stable';
            }

            return $updateChannel === $channel;
        }));
    }

    /**
     * @param array<int, array<string, mixed>> $updates
     */
    public function storePending(array $updates): void
    {
        Storage::disk('local')->put('updates/pending.json', json_encode([
            'updated_at' => now()->toIso8601String(),
            'updates' => $updates,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPendingUpdates(): array
    {
        if (!Storage::disk('local')->exists('updates/pending.json')) {
            return [];
        }

        $payload = json_decode(Storage::disk('local')->get('updates/pending.json'), true);

        return is_array($payload['updates'] ?? null) ? $payload['updates'] : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPendingUpdate(string $id): ?array
    {
        foreach ($this->getPendingUpdates() as $update) {
            if (($update['id'] ?? null) === $id) {
                return $update;
            }
        }

        return null;
    }

    public function removePendingUpdate(string $id): void
    {
        $updates = array_values(array_filter($this->getPendingUpdates(), function (array $update) use ($id): bool {
            return ($update['id'] ?? null) !== $id;
        }));

        $this->storePending($updates);
    }

    public function applyUpdate(array $update): void
    {
        $type = $update['type'] ?? null;

        if ($type === 'module') {
            $this->applyModuleUpdate($update);

            return;
        }

        $this->applyCoreUpdate($update);
    }

    private function applyCoreUpdate(array $update): void
    {
        $record = [
            'applied_at' => now()->toIso8601String(),
            'update' => $update,
        ];

        Storage::disk('local')->put('updates/applied-'.$update['id'].'.json', json_encode($record, JSON_PRETTY_PRINT));
    }

    private function applyModuleUpdate(array $update): void
    {
        $moduleId = $update['module_id'] ?? null;
        $downloadUrl = $update['download_url'] ?? null;

        if (!$moduleId || !$downloadUrl) {
            throw new \RuntimeException('Module update missing module_id or download_url.');
        }

        $response = Http::timeout(20)->retry(2, 250)->get($downloadUrl);

        if (!$response->ok()) {
            throw new \RuntimeException('Failed to download module update.');
        }

        $downloadPath = storage_path('app/updates/downloads/'.$update['id'].'.zip');
        File::ensureDirectoryExists(dirname($downloadPath));
        File::put($downloadPath, $response->body());

        $extractPath = storage_path('app/modules/'.$moduleId);
        File::ensureDirectoryExists($extractPath);

        $zip = new ZipArchive();

        if ($zip->open($downloadPath) !== true) {
            throw new \RuntimeException('Failed to open module update archive.');
        }

        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            throw new \RuntimeException('Failed to extract module update.');
        }

        $zip->close();

        $record = [
            'applied_at' => now()->toIso8601String(),
            'update' => $update,
        ];

        Storage::disk('local')->put('updates/applied-'.$update['id'].'.json', json_encode($record, JSON_PRETTY_PRINT));
    }
}
