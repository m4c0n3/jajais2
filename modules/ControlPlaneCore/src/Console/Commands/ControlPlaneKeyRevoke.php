<?php

namespace Modules\ControlPlaneCore\Console\Commands;

use App\Support\Audit\AuditService;
use Illuminate\Console\Command;
use Modules\ControlPlaneCore\Models\SigningKey;

class ControlPlaneKeyRevoke extends Command
{
    protected $signature = 'control-plane:key:revoke {kid}';
    protected $description = 'Revoke a signing key by kid.';

    public function handle(): int
    {
        $kid = (string) $this->argument('kid');
        $key = SigningKey::query()->where('kid', $kid)->first();

        if (!$key) {
            $this->error('Signing key not found.');

            return Command::FAILURE;
        }

        if (!$key->active) {
            $this->info('Signing key already inactive: '.$kid);

            return Command::SUCCESS;
        }

        $key->update(['active' => false]);

        $this->info('Signing key revoked: '.$kid);
        $this->logAudit('control_plane.signing_key_revoked', ['kid' => $kid]);

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
