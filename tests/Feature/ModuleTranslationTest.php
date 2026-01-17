<?php

namespace Tests\Feature;

use App\Support\I18n\ModuleTranslationLoader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_translations_load_and_fallback(): void
    {
        DB::table('modules')->insert([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'enabled' => true,
            'installed_version' => '1.0.0',
            'requires_core' => null,
            'license_required' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ModuleTranslationLoader::class)->loadActiveModuleTranslations();

        app()->setLocale('sk');

        $this->assertSame('Ahoj svet', trans('HelloWorld::messages.greeting'));
        $this->assertSame('Goodbye', trans('HelloWorld::messages.farewell'));
    }
}
