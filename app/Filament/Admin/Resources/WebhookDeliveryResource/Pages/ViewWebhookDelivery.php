<?php

namespace App\Filament\Admin\Resources\WebhookDeliveryResource\Pages;

use App\Filament\Admin\Resources\WebhookDeliveryResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewWebhookDelivery extends ViewRecord
{
    protected static string $resource = WebhookDeliveryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Details')->schema([
                    KeyValueEntry::make('payload')
                        ->columnSpanFull(),
                    TextEntry::make('last_error')
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
