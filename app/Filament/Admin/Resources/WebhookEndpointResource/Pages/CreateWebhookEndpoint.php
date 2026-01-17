<?php

namespace App\Filament\Admin\Resources\WebhookEndpointResource\Pages;

use App\Filament\Admin\Resources\WebhookEndpointResource;
use App\Support\Audit\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateWebhookEndpoint extends CreateRecord
{
    protected static string $resource = WebhookEndpointResource::class;

    protected function afterCreate(): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log('webhook.endpoint_created', [
                'target_type' => 'webhook_endpoint',
                'target_id' => (string) $this->record->id,
            ]);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
