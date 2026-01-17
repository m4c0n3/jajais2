<?php

namespace App\Support\I18n;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Translation\Translator;

class ModuleTranslationLoader
{
    public function __construct(private ModuleBootManager $bootManager, private Translator $translator)
    {
    }

    public function loadActiveModuleTranslations(): void
    {
        foreach ($this->bootManager->getActiveModules() as $module) {
            $moduleId = $module['id'] ?? null;

            if (!$moduleId || !is_string($moduleId)) {
                continue;
            }

            $path = base_path('modules/'.$moduleId.'/lang');

            if (!is_dir($path)) {
                continue;
            }

            $this->translator->addNamespace($moduleId, $path);
        }
    }
}
