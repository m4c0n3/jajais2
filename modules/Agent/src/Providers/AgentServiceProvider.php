<?php

namespace Modules\Agent\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Agent\Console\Commands\AgentHeartbeatCommand;
use Modules\Agent\Console\Commands\AgentLicenseRefreshCommand;
use Modules\Agent\Console\Commands\AgentRegisterCommand;
use Modules\Agent\Console\Commands\AgentStatusCommand;

class AgentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/agent.php', 'agent');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AgentRegisterCommand::class,
                AgentHeartbeatCommand::class,
                AgentLicenseRefreshCommand::class,
                AgentStatusCommand::class,
            ]);
        }

        $this->registerSchedule();
    }

    private function registerSchedule(): void
    {
        if (!config('agent.enabled')) {
            return;
        }

        if (!config('agent.schedule.enabled')) {
            return;
        }

        $heartbeatCron = (string) config('agent.schedule.heartbeat_cron', '* * * * *');
        $licenseCron = (string) config('agent.schedule.license_refresh_cron', '0 * * * *');

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) use ($heartbeatCron, $licenseCron): void {
            $schedule->command('agent:heartbeat')->cron($heartbeatCron)->withoutOverlapping();
            $schedule->command('agent:license-refresh')->cron($licenseCron)->withoutOverlapping();
        });
    }
}
