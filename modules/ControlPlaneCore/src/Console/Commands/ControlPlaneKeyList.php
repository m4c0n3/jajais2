<?php

namespace Modules\ControlPlaneCore\Console\Commands;

use Illuminate\Console\Command;
use Modules\ControlPlaneCore\Models\SigningKey;

class ControlPlaneKeyList extends Command
{
    protected $signature = 'control-plane:key:list';
    protected $description = 'List signing keys.';

    public function handle(): int
    {
        $keys = SigningKey::query()->orderByDesc('id')->get();

        if ($keys->isEmpty()) {
            $this->info('No signing keys found.');

            return Command::SUCCESS;
        }

        $this->table(['kid', 'active', 'created_at'], $keys->map(function (SigningKey $key) {
            return [$key->kid, $key->active ? 'yes' : 'no', $key->created_at?->toIso8601String()];
        })->all());

        return Command::SUCCESS;
    }
}
