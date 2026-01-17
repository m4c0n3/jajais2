<?php

namespace App\Console\Commands;

use App\Support\System\SystemInstaller;
use Illuminate\Console\Command;

class SystemInstall extends Command
{
    protected $signature = 'system:install
        {--mode= : client or control-plane}
        {--admin-name= : Admin name}
        {--admin-email= : Admin email}
        {--admin-password= : Admin password}
        {--non-interactive : Do not prompt for missing options}';

    protected $description = 'Run initial system installation.';

    public function handle(SystemInstaller $installer): int
    {
        $mode = (string) ($this->option('mode') ?? '');
        $name = (string) ($this->option('admin-name') ?? '');
        $email = (string) ($this->option('admin-email') ?? '');
        $password = (string) ($this->option('admin-password') ?? '');
        $nonInteractive = (bool) $this->option('non-interactive');

        if ($mode === '' && !$nonInteractive) {
            $mode = $this->choice('Select mode', ['client', 'control-plane'], 0);
        }

        if ($name === '' && !$nonInteractive) {
            $name = (string) $this->ask('Admin name', 'Admin');
        }

        if ($email === '' && !$nonInteractive) {
            $email = (string) $this->ask('Admin email');
        }

        if ($password === '' && !$nonInteractive) {
            $password = (string) $this->secret('Admin password');
        }

        if ($mode === '' || $name === '' || $email === '' || $password === '') {
            $this->error('Missing required options. Use --non-interactive with all values.');

            return Command::FAILURE;
        }

        try {
            $installer->install($mode, $name, $email, $password);
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            return Command::FAILURE;
        }

        $this->info('System installation complete.');

        return Command::SUCCESS;
    }
}
