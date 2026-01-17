<?php

namespace Modules\Agent\Services;

use App\Support\Audit\AuditService;
use App\Support\Licensing\LicenseService;
use App\Support\Licensing\JwtTokenVerifier;
use App\Support\Webhooks\WebhookDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AgentService
{
    public function __construct(private ControlPlaneClient $client)
    {
    }

    public function register(): array
    {
        $this->assertInstanceStateTable();

        $state = $this->getInstanceState();
        $instanceUuid = $state?->instance_uuid ?? (string) Str::uuid();

        $payload = [
            'app_env' => config('app.env'),
            'app_version' => config('app.version'),
            'php_version' => PHP_VERSION,
            'timestamp' => CarbonImmutable::now()->toIso8601String(),
        ];

        $response = $this->client->register($payload);

        if ($response->failed()) {
            $this->updateLastError($instanceUuid, 'Register failed: '.$response->status());

            return ['ok' => false, 'message' => 'Register failed: '.$response->status()];
        }

        $data = $response->json();
        $instanceUuid = $data['instance_uuid'] ?? $instanceUuid;

        $this->upsertInstanceState([
            'instance_uuid' => $instanceUuid,
            'registered_at' => CarbonImmutable::now(),
            'last_error' => null,
        ]);

        return ['ok' => true, 'instance_uuid' => $instanceUuid];
    }

    public function sendHeartbeat(): array
    {
        $state = $this->ensureInstanceUuid();
        $payload = $this->buildHeartbeatPayload($state['instance_uuid']);

        $response = $this->client->heartbeat($state['instance_uuid'], $payload);

        if ($response->failed()) {
            $this->updateLastError($state['instance_uuid'], 'Heartbeat failed: '.$response->status());

            return ['ok' => false, 'message' => 'Heartbeat failed: '.$response->status()];
        }

        $this->upsertInstanceState([
            'instance_uuid' => $state['instance_uuid'],
            'last_heartbeat_at' => CarbonImmutable::now(),
            'last_error' => null,
        ]);

        return ['ok' => true];
    }

    public function refreshLicenseToken(): array
    {
        $state = $this->ensureInstanceUuid();

        $payload = [
            'instance_uuid' => $state['instance_uuid'],
            'timestamp' => CarbonImmutable::now()->toIso8601String(),
        ];

        $response = $this->client->refreshLicense($state['instance_uuid'], $payload);

        if ($response->failed()) {
            $this->updateLastError($state['instance_uuid'], 'License refresh failed: '.$response->status());

            return ['ok' => false, 'message' => 'License refresh failed: '.$response->status()];
        }

        $data = $response->json();
        $token = is_array($data) ? ($data['token'] ?? null) : null;

        if (!is_string($token) || $token === '') {
            $this->updateLastError($state['instance_uuid'], 'License refresh missing token.');

            return ['ok' => false, 'message' => 'License refresh missing token.'];
        }

        try {
            $audience = config('agent.jwt_audience') ?: $state['instance_uuid'];
            $claims = app(JwtTokenVerifier::class)->verify($token, $audience);
        } catch (\Throwable $exception) {
            $this->recordInvalidToken($token, $exception->getMessage());
            $this->updateLastError($state['instance_uuid'], 'License token invalid.');
            $this->logAudit('license.token_invalid', [
                'reason' => $exception->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'License token invalid.'];
        }

        $validTo = $this->parseDate($claims['valid_to'] ?? null) ?? $this->parseDate($data['valid_to'] ?? null) ?? CarbonImmutable::now();
        $graceTo = $this->parseDate($claims['grace_to'] ?? null) ?? $this->parseDate($data['grace_to'] ?? null);

        DB::table('license_tokens')->insert([
            'fetched_at' => CarbonImmutable::now(),
            'valid_to' => $validTo,
            'grace_to' => $graceTo,
            'token' => $token,
            'parsed' => json_encode($claims),
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $this->upsertInstanceState([
            'instance_uuid' => $state['instance_uuid'],
            'last_license_refresh_at' => CarbonImmutable::now(),
            'last_error' => null,
        ]);

        $this->clearLicenseCache();
        $this->dispatchWebhook('license.updated', [
            'valid_to' => $validTo?->toIso8601String(),
            'grace_to' => $graceTo?->toIso8601String(),
        ]);

        return ['ok' => true];
    }

    public function buildHeartbeatPayload(string $instanceUuid): array
    {
        return [
            'instance_uuid' => $instanceUuid,
            'app_env' => config('app.env'),
            'app_version' => config('app.version'),
            'php_version' => PHP_VERSION,
            'modules' => $this->getModulesSummary(),
            'license' => $this->getLicenseMeta(),
            'timestamp' => CarbonImmutable::now()->toIso8601String(),
        ];
    }

    private function getModulesSummary(): array
    {
        if (!Schema::hasTable('modules')) {
            return [];
        }

        return DB::table('modules')
            ->get(['id', 'installed_version', 'enabled', 'license_required'])
            ->map(fn ($module) => [
                'id' => $module->id,
                'version' => $module->installed_version,
                'enabled' => (bool) $module->enabled,
                'license_required' => (bool) $module->license_required,
            ])
            ->all();
    }

    private function getLicenseMeta(): array
    {
        if (!Schema::hasTable('license_tokens')) {
            return [
                'valid_to' => null,
                'grace_to' => null,
                'is_valid' => false,
                'is_grace' => false,
            ];
        }

        $licenseService = app(LicenseService::class);
        $meta = $licenseService->getMeta();

        return [
            'valid_to' => $meta['valid_to']?->toIso8601String(),
            'grace_to' => $meta['grace_to']?->toIso8601String(),
            'is_valid' => $licenseService->isLicenseValid(),
            'is_grace' => $licenseService->isInGrace(),
        ];
    }

    private function ensureInstanceUuid(): array
    {
        $this->assertInstanceStateTable();

        $state = $this->getInstanceState();

        if ($state && $state->instance_uuid) {
            return [
                'instance_uuid' => $state->instance_uuid,
            ];
        }

        $instanceUuid = (string) Str::uuid();

        $this->upsertInstanceState([
            'instance_uuid' => $instanceUuid,
            'last_error' => null,
        ]);

        return ['instance_uuid' => $instanceUuid];
    }

    private function getInstanceState(): ?object
    {
        return DB::table('instance_state')->orderByDesc('id')->first();
    }

    private function upsertInstanceState(array $data): void
    {
        $state = $this->getInstanceState();
        $payload = array_merge([
            'instance_uuid' => $data['instance_uuid'],
            'registered_at' => $data['registered_at'] ?? $state?->registered_at,
            'last_heartbeat_at' => $data['last_heartbeat_at'] ?? $state?->last_heartbeat_at,
            'last_license_refresh_at' => $data['last_license_refresh_at'] ?? $state?->last_license_refresh_at,
            'last_error' => $data['last_error'] ?? $state?->last_error,
            'updated_at' => CarbonImmutable::now(),
        ], $state ? [] : ['created_at' => CarbonImmutable::now()]);

        if ($state) {
            DB::table('instance_state')->where('id', $state->id)->update($payload);
        } else {
            DB::table('instance_state')->insert($payload);
        }
    }

    private function updateLastError(string $instanceUuid, string $message): void
    {
        $this->upsertInstanceState([
            'instance_uuid' => $instanceUuid,
            'last_error' => $message,
        ]);
    }

    private function clearLicenseCache(): void
    {
        if (class_exists(LicenseService::class)) {
            app(LicenseService::class)->clearCache();
        }
    }

    private function recordInvalidToken(string $token, string $error): void
    {
        if (!Schema::hasTable('license_tokens')) {
            return;
        }

        DB::table('license_tokens')->insert([
            'fetched_at' => CarbonImmutable::now(),
            'valid_to' => CarbonImmutable::now(),
            'grace_to' => null,
            'token' => $token,
            'parsed' => null,
            'revoked_at' => CarbonImmutable::now(),
            'last_refresh_error' => $error,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);
    }

    private function logAudit(string $action, array $metadata = []): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action, [
                'metadata' => $metadata,
            ]);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }

    private function dispatchWebhook(string $event, array $data): void
    {
        if (!class_exists(WebhookDispatcher::class)) {
            return;
        }

        try {
            app(WebhookDispatcher::class)->dispatch($event, $data);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function assertInstanceStateTable(): void
    {
        if (!Schema::hasTable('instance_state')) {
            throw new \RuntimeException('instance_state table missing. Run migrations.');
        }
    }
}
