<?php

namespace Modules\Agent\Console\Commands;

use App\Support\Licensing\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgentStatusCommand extends Command
{
    protected $signature = 'agent:status';
    protected $description = 'Show agent instance status and license meta';

    public function handle(): int
    {
        if (!Schema::hasTable('instance_state')) {
            $this->error('instance_state table missing. Run migrations.');

            return self::FAILURE;
        }

        $state = DB::table('instance_state')->orderByDesc('id')->first();

        if (!$state) {
            $this->warn('No instance state found. Run agent:register.');

            return self::SUCCESS;
        }

        $this->line('Instance UUID: '.$state->instance_uuid);
        $this->line('Registered at: '.($state->registered_at ?? '-'));
        $this->line('Last heartbeat: '.($state->last_heartbeat_at ?? '-'));
        $this->line('Last license refresh: '.($state->last_license_refresh_at ?? '-'));
        $this->line('Last error: '.($state->last_error ?? '-'));

        if (class_exists(LicenseService::class)) {
            $licenseService = app(LicenseService::class);
            $meta = $licenseService->getMeta();

            $this->line('License valid_to: '.($meta['valid_to']?->toIso8601String() ?? '-'));
            $this->line('License grace_to: '.($meta['grace_to']?->toIso8601String() ?? '-'));
            $this->line('License is_valid: '.($licenseService->isLicenseValid() ? 'yes' : 'no'));
            $this->line('License is_grace: '.($licenseService->isInGrace() ? 'yes' : 'no'));
        }

        return self::SUCCESS;
    }
}
