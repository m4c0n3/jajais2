<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WebhookEndpointResource\Pages\CreateWebhookEndpoint;
use App\Filament\Admin\Resources\WebhookEndpointResource\Pages\EditWebhookEndpoint;
use App\Filament\Admin\Resources\WebhookEndpointResource\Pages\ListWebhookEndpoints;
use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Support\Audit\AuditService;
use App\Support\Observability\RequestContext;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebhookEndpointResource extends Resource
{
    protected static ?string $model = WebhookEndpoint::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Webhooks';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('url')->required()->url(),
                    Toggle::make('is_active')->label('Active'),
                    Select::make('events')
                        ->multiple()
                        ->options(self::eventOptions())
                        ->required(),
                    TextInput::make('timeout_seconds')->numeric()->default(10),
                    TextInput::make('max_attempts')->numeric()->default(10),
                    TextInput::make('secret')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText('Leave empty to keep existing secret.')
                        ->suffixAction(FormAction::make('regenerate')
                            ->label('Regenerate')
                            ->action(fn ($set) => $set('secret', Str::random(48)))),
                    KeyValue::make('headers')->addButtonLabel('Add header'),
                    KeyValue::make('backoff_seconds')->addButtonLabel('Add delay'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('url')->limit(40),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('events')->formatStateUsing(fn ($state) => implode(', ', $state ?? [])),
                TextColumn::make('secret')->label('Secret')->formatStateUsing(fn () => '••••••'),
                TextColumn::make('last_success_at')->dateTime(),
                TextColumn::make('last_failure_at')->dateTime(),
            ])
            ->actions([
                Action::make('test')
                    ->label('Test')
                    ->visible(fn () => auth()->user()?->hasRole('super-admin') || auth()->user()?->can('webhooks.manage'))
                    ->action(function (WebhookEndpoint $record): void {
                        $payload = [
                            'id' => (string) Str::uuid(),
                            'event' => 'webhook.test',
                            'occurred_at' => CarbonImmutable::now()->toIso8601String(),
                            'actor' => ['type' => 'system', 'id' => null],
                            'data' => ['test' => true],
                        ];

                        $delivery = WebhookDelivery::create([
                            'webhook_endpoint_id' => $record->id,
                            'event' => 'webhook.test',
                            'payload' => $payload,
                            'status' => 'pending',
                            'attempt' => 0,
                            'correlation_id' => $payload['id'],
                        ]);

                        SendWebhookDeliveryJob::dispatch($delivery->id, RequestContext::currentRequestId());
                        self::logAudit('webhook.endpoint_tested', [
                            'target_type' => 'webhook_endpoint',
                            'target_id' => (string) $record->id,
                        ]);
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookEndpoints::route('/'),
            'create' => CreateWebhookEndpoint::route('/create'),
            'edit' => EditWebhookEndpoint::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super-admin') || $user?->can('webhooks.view');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super-admin') || $user?->can('webhooks.manage');
    }

    public static function canEdit($record): bool
    {
        return self::canCreate();
    }

    public static function canDelete($record): bool
    {
        return self::canCreate();
    }

    private static function logAudit(string $action, array $context): void
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

    private static function eventOptions(): array
    {
        return [
            'module.enabled' => 'module.enabled',
            'module.disabled' => 'module.disabled',
            'rbac.synced' => 'rbac.synced',
            'license.updated' => 'license.updated',
            'update.available' => 'update.available',
            'update.applied' => 'update.applied',
            'update.failed' => 'update.failed',
            'agent.heartbeat_failed' => 'agent.heartbeat_failed',
            'webhook.test' => 'webhook.test',
        ];
    }
}
