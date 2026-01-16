<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureModuleActive;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnsureModuleActiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureModulesTable();
        Cache::forget('license.active');
    }

    public function test_disabled_module_is_blocked(): void
    {
        Route::middleware(EnsureModuleActive::class.':blog')->get('/blog', fn () => 'ok');

        DB::table('modules')->insert([
            'id' => 'blog',
            'name' => 'Blog',
            'enabled' => false,
            'license_required' => false,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $this->get('/blog')->assertStatus(403);
    }

    public function test_licensed_module_without_entitlement_is_blocked(): void
    {
        Route::middleware(EnsureModuleActive::class.':shop')->get('/shop', fn () => 'ok');

        DB::table('modules')->insert([
            'id' => 'shop',
            'name' => 'Shop',
            'enabled' => true,
            'license_required' => true,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $this->get('/shop')->assertStatus(403);
    }

    public function test_licensed_module_with_entitlement_is_allowed(): void
    {
        Route::middleware(EnsureModuleActive::class.':chat')->get('/chat', fn () => 'ok');

        DB::table('modules')->insert([
            'id' => 'chat',
            'name' => 'Chat',
            'enabled' => true,
            'license_required' => true,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        DB::table('license_tokens')->insert([
            'fetched_at' => CarbonImmutable::now(),
            'valid_to' => CarbonImmutable::now()->addDay(),
            'grace_to' => CarbonImmutable::now()->addDays(2),
            'token' => '{"modules":["chat"],"valid_to":"2030-01-01T00:00:00Z"}',
            'parsed' => json_encode([
                'modules' => ['chat'],
                'valid_to' => '2030-01-01T00:00:00Z',
                'grace_to' => '2030-01-02T00:00:00Z',
            ]),
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        Cache::forget('license.active');

        $this->get('/chat')->assertOk();
    }

    private function ensureModulesTable(): void
    {
        if (Schema::hasTable('modules')) {
            return;
        }

        Schema::create('modules', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->boolean('enabled')->default(false);
            $table->boolean('license_required')->default(false);
            $table->timestamps();
        });
    }
}
