<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleBootManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ModuleDisable extends Command
{
    protected $signature = 'module:disable {id}';
    protected $description = 'Disable a module by id';

    public function handle(ModuleBootManager $bootManager): int
    {
        $id = (string) $this->argument('id');

        $module = DB::table('modules')->where('id', $id)->first();

        if (!$module) {
            $this->error('Module not found. Run module:discover first.');

            return self::FAILURE;
        }

        DB::table('modules')->where('id', $id)->update(['enabled' => false]);
        $bootManager->clearCache();

        $this->info("Module disabled: {$id}");

        return self::SUCCESS;
    }
}
