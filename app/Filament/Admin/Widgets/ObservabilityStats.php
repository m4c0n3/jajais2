<?php

namespace App\Filament\Admin\Widgets;

use App\Support\System\AppMode;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ObservabilityStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $now = CarbonImmutable::now();

        $stats[] = Stat::make('Webhook failures (24h)', $this->failedWebhooksLastDay($now));
        $stats[] = Stat::make('License expired', $this->expiredLicenses($now));

        if (app(AppMode::class)->isControlPlane()) {
            $stats[] = Stat::make('Stale instances (15m)', $this->staleInstances($now));
        } else {
            $stats[] = Stat::make('Last agent heartbeat', $this->lastAgentHeartbeat());
        }

        return $stats;
    }

    private function failedWebhooksLastDay(CarbonImmutable $now): string
    {
        if (!Schema::hasTable('webhook_deliveries')) {
            return 'n/a';
        }

        $count = DB::table('webhook_deliveries')
            ->where('status', 'failed')
            ->where('created_at', '>=', $now->subDay())
            ->count();

        return (string) $count;
    }

    private function expiredLicenses(CarbonImmutable $now): string
    {
        if (!Schema::hasTable('license_tokens')) {
            return 'n/a';
        }

        $count = DB::table('license_tokens')
            ->where('valid_to', '<', $now)
            ->where(function ($query) use ($now): void {
                $query->whereNull('grace_to')
                    ->orWhere('grace_to', '<', $now);
            })
            ->count();

        return (string) $count;
    }

    private function lastAgentHeartbeat(): string
    {
        if (!Schema::hasTable('instance_state')) {
            return 'n/a';
        }

        $state = DB::table('instance_state')->orderByDesc('id')->first();

        if (!$state?->last_heartbeat_at) {
            return 'never';
        }

        return CarbonImmutable::parse($state->last_heartbeat_at)->diffForHumans();
    }

    private function staleInstances(CarbonImmutable $now): string
    {
        if (!Schema::hasTable('cp_instances')) {
            return 'n/a';
        }

        $threshold = $now->subMinutes(15);

        $count = DB::table('cp_instances')
            ->where(function ($query) use ($threshold): void {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $threshold);
            })
            ->count();

        return (string) $count;
    }
}
