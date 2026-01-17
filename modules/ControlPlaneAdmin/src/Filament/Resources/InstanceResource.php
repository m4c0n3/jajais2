<?php

namespace Modules\ControlPlaneAdmin\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\ControlPlaneAdmin\Filament\Resources\InstanceResource\Pages\ListInstances;
use Modules\ControlPlaneCore\Models\Instance;

class InstanceResource extends Resource
{
    protected static ?string $model = Instance::class;
    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationGroup = 'Control Plane';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->maxLength(255),
            TextInput::make('uuid')->disabled(),
            TextInput::make('status'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->searchable(),
            TextColumn::make('name'),
            TextColumn::make('status'),
            TextColumn::make('last_seen_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstances::route('/'),
        ];
    }
}
