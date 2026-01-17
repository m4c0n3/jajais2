<?php

namespace Modules\ControlPlaneAdmin\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\ControlPlaneAdmin\Filament\Resources\EntitlementResource\Pages\ListEntitlements;
use Modules\ControlPlaneCore\Models\Entitlement;

class EntitlementResource extends Resource
{
    protected static ?string $model = Entitlement::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Control Plane';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('instance_id')->disabled(),
            TextInput::make('module_id'),
            TextInput::make('valid_to'),
            TextInput::make('grace_to'),
            Toggle::make('enabled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('instance_id'),
            TextColumn::make('module_id'),
            TextColumn::make('valid_to')->dateTime(),
            TextColumn::make('grace_to')->dateTime(),
            TextColumn::make('enabled'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntitlements::route('/'),
        ];
    }
}
