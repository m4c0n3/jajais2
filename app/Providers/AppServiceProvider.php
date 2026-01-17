<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use App\Support\Modules\ModuleMigrationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-token', function (Request $request): Limit {
            $tokenId = $request->user()?->currentAccessToken()?->id;
            $key = $tokenId ? 'token:'.$tokenId : 'ip:'.$request->ip();

            return Limit::perMinute(60)->by($key);
        });

        Event::listen(CommandStarting::class, function (): void {
            try {
                app(ModuleMigrationManager::class)->registerEnabledMigrations();
            } catch (\Throwable) {
                // Best-effort only; migrations will still run for core.
            }
        });

        $this->app->afterResolving('migrator', function (): void {
            try {
                app(ModuleMigrationManager::class)->registerEnabledMigrations();
            } catch (\Throwable) {
                // Best-effort only; migrations will still run for core.
            }
        });
    }
}
