<?php

namespace App\Support\Ops;

use App\Support\Licensing\LicenseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemStatusService
{
    public function __construct(private LicenseService $licenseService)
    {
    }

    public function summary(): array
    {
        [$dbOk, $dbError] = $this->checkDatabase();
        [$lastHeartbeat, $lastLicenseRefresh] = $this->getAgentTimestamps();
        [$licenseStatus, $validTo, $graceTo] = $this->getLicenseSummary();

        return [
            'db' => [
                'ok' => $dbOk,
                'error' => $dbError,
            ],
            'queue_driver' => config('queue.default'),
            'agent' => [
                'last_heartbeat_at' => $lastHeartbeat,
                'last_license_refresh_at' => $lastLicenseRefresh,
            ],
            'webhooks' => [
                'failed_deliveries' => $this->getFailedWebhookCount(),
            ],
            'license' => [
                'status' => $licenseStatus,
                'valid_to' => $validTo,
                'grace_to' => $graceTo,
            ],
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');

            return [true, null];
        } catch (\Throwable $error) {
            return [false, $error->getMessage()];
        }
    }

    private function getAgentTimestamps(): array
    {
        if (!Schema::hasTable('instance_state')) {
            return [null, null];
        }

        $state = DB::table('instance_state')->orderByDesc('id')->first();

        return [
            $state?->last_heartbeat_at ? (string) $state->last_heartbeat_at : null,
            $state?->last_license_refresh_at ? (string) $state->last_license_refresh_at : null,
        ];
    }

    private function getFailedWebhookCount(): ?int
    {
        if (!Schema::hasTable('webhook_deliveries')) {
            return null;
        }

        return (int) DB::table('webhook_deliveries')->where('status', 'failed')->count();
    }

    private function getLicenseSummary(): array
    {
        if (!Schema::hasTable('license_tokens')) {
            return ['n/a', null, null];
        }

        $valid = $this->licenseService->isLicenseValid();
        $grace = $this->licenseService->isInGrace();
        $meta = $this->licenseService->getMeta();

        $validTo = $meta['valid_to']?->toIso8601String();
        $graceTo = $meta['grace_to']?->toIso8601String();

        if ($valid) {
            return ['valid', $validTo, $graceTo];
        }

        if ($grace) {
            return ['grace', $validTo, $graceTo];
        }

        return ['invalid', $validTo, $graceTo];
    }
}
