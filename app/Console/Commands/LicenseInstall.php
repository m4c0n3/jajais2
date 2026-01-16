<?php

namespace App\Console\Commands;

use App\Support\Licensing\LicenseService;
use App\Support\Audit\AuditService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LicenseInstall extends Command
{
    protected $signature = 'license:install {--token=} {--file=}';
    protected $description = 'Install a license token into the local cache';

    public function handle(LicenseService $licenseService): int
    {
        $token = $this->option('token');
        $file = $this->option('file');

        if (!$token && !$file) {
            $this->error('Provide --token or --file.');

            return self::FAILURE;
        }

        if ($token && $file) {
            $this->error('Use either --token or --file, not both.');

            return self::FAILURE;
        }

        if ($file) {
            if (!is_string($file) || !is_file($file)) {
                $this->error('License file not found.');

                return self::FAILURE;
            }

            $token = trim((string) file_get_contents($file));
        }

        $token = is_string($token) ? trim($token) : '';

        if ($token === '') {
            $this->error('Token is empty.');

            return self::FAILURE;
        }

        $parsed = $licenseService->parseToken($token);
        [$validTo, $graceTo] = $licenseService->extractDates($parsed);

        if (!$validTo) {
            $this->warn('Token missing valid_to; storing as expired.');
            $validTo = CarbonImmutable::now();
        }

        DB::table('license_tokens')->insert([
            'fetched_at' => CarbonImmutable::now(),
            'valid_to' => $validTo,
            'grace_to' => $graceTo,
            'token' => $token,
            'parsed' => $parsed ? json_encode($parsed) : null,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $licenseService->clearCache();

        $this->info('License token installed.');
        $this->logAudit('license.install');

        return self::SUCCESS;
    }

    private function logAudit(string $action): void
    {
        if (!class_exists(AuditService::class)) {
            return;
        }

        try {
            app(AuditService::class)->log($action);
        } catch (\Throwable) {
            // Best-effort only.
        }
    }
}
