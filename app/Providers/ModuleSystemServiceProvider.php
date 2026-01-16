<?php

namespace App\Providers;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Support\ServiceProvider;

class ModuleSystemServiceProvider extends ServiceProvider
{
    public function boot(ModuleBootManager $bootManager): void
    {
        $bootManager->bootActiveModules();
    }
}
