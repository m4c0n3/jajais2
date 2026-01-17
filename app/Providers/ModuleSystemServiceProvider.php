<?php

namespace App\Providers;

use App\Support\I18n\ModuleTranslationLoader;
use App\Support\Modules\ModuleBootManager;
use Illuminate\Support\ServiceProvider;

class ModuleSystemServiceProvider extends ServiceProvider
{
    public function boot(ModuleBootManager $bootManager, ModuleTranslationLoader $translationLoader): void
    {
        $bootManager->bootActiveModules();
        $translationLoader->loadActiveModuleTranslations();
    }
}
