<?php

namespace Modules\ControlPlaneAdmin\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\ControlPlaneAdmin\Filament\Resources\SigningKeyResource\Pages\ListSigningKeys;
use Modules\ControlPlaneCore\Models\SigningKey;

class SigningKeyResource extends Resource
{
    protected static ?string $model = SigningKey::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Control Plane';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('kid')->disabled(),
            TextInput::make('active')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('kid'),
            TextColumn::make('active'),
            TextColumn::make('created_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSigningKeys::route('/'),
        ];
    }
}
