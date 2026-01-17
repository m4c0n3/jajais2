<?php

namespace Modules\ControlPlaneAdmin\Providers;

use Filament\Panel;
use Filament\PanelProvider;

class ControlPlaneAdminServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->discoverResources(in: __DIR__.'/../Filament/Resources', for: 'Modules\\ControlPlaneAdmin\\Filament\\Resources');
    }
}
