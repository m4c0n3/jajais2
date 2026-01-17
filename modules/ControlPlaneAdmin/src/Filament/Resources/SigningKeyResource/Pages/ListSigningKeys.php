<?php

namespace Modules\ControlPlaneAdmin\Filament\Resources\SigningKeyResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\ControlPlaneAdmin\Filament\Resources\SigningKeyResource;

class ListSigningKeys extends ListRecords
{
    protected static string $resource = SigningKeyResource::class;
}
