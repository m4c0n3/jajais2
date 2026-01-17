<?php

use App\Models\WebhookEndpoint;
use App\Support\Audit\AuditService;
use App\Support\Ops\SystemStatusService;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api-token'])->group(function () {
    Route::get('/system/status', function (SystemStatusService $statusService) {
        $summary = $statusService->summary();

        if (class_exists(AuditService::class)) {
            try {
                app(AuditService::class)->log('api.system_status_viewed');
            } catch (\Throwable) {
                // Best-effort audit logging only.
            }
        }

        return response()->json($summary);
    })->middleware('can:system.status.view');

    Route::get('/webhooks/endpoints', function () {
        $endpoints = WebhookEndpoint::query()
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'url',
                'is_active',
                'events',
                'last_success_at',
                'last_failure_at',
            ]);

        if (class_exists(AuditService::class)) {
            try {
                app(AuditService::class)->log('api.webhooks_endpoints_viewed');
            } catch (\Throwable) {
                // Best-effort audit logging only.
            }
        }

        return response()->json([
            'data' => $endpoints,
        ]);
    })->middleware('can:webhooks.view');
});
