<?php

namespace App\Filament\Admin\Resources\WebhookEndpointResource\Pages;

use App\Filament\Admin\Resources\WebhookEndpointResource;
use App\Support\Audit\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebhookEndpoint extends EditRecord
{
    protected static string $resource = WebhookEndpointResource::class;

    protected function afterSave(): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log('webhook.endpoint_updated', [
                'target_type' => 'webhook_endpoint',
                'target_id' => (string) $this->record->id,
            ]);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (): void {
                    if (!class_exists(AuditService::class)) {
                        return;
                    }

                    try {
                        app(AuditService::class)->log('webhook.endpoint_deleted', [
                            'target_type' => 'webhook_endpoint',
                            'target_id' => (string) $this->record->id,
                        ]);
                    } catch (\Throwable) {
                        // Best-effort only.
                    }
                }),
        ];
    }
}
