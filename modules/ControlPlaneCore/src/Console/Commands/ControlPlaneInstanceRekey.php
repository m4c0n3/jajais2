<?php

namespace Modules\ControlPlaneCore\Console\Commands;

use App\Support\Audit\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\ControlPlaneCore\Models\Instance;

class ControlPlaneInstanceRekey extends Command
{
    protected $signature = 'control-plane:instance:rekey {uuid}';
    protected $description = 'Regenerate the API secret for an instance.';

    public function handle(): int
    {
        $uuid = (string) $this->argument('uuid');
        $instance = Instance::query()->where('uuid', $uuid)->first();

        if (!$instance) {
            $this->error('Instance not found.');

            return Command::FAILURE;
        }

        $secret = Str::random(48);
        $instance->update([
            'api_key_hash' => Hash::make($secret),
        ]);

        $this->info('New instance secret: '.$secret);
        $this->logAudit('control_plane.instance_rekeyed', ['instance_uuid' => $uuid]);

        return Command::SUCCESS;
    }

    private function logAudit(string $action, array $context): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action, $context);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
