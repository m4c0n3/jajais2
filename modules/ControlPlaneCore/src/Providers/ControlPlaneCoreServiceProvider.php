<?php

namespace Modules\ControlPlaneCore\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ControlPlaneCore\Console\Commands\ControlPlaneKeyList;
use Modules\ControlPlaneCore\Console\Commands\ControlPlaneKeyRevoke;
use Modules\ControlPlaneCore\Console\Commands\ControlPlaneKeyRotate;
use Modules\ControlPlaneCore\Console\Commands\ControlPlaneInstanceRekey;

class ControlPlaneCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/control_plane.php', 'control_plane');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ControlPlaneKeyRotate::class,
                ControlPlaneKeyList::class,
                ControlPlaneKeyRevoke::class,
                ControlPlaneInstanceRekey::class,
            ]);
        }
    }
}
