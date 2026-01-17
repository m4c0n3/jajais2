<?php

namespace App\Console\Commands;

use App\Models\WebhookEndpoint;
use Illuminate\Console\Command;

class WebhookList extends Command
{
    protected $signature = 'webhook:list';
    protected $description = 'List configured webhook endpoints';

    public function handle(): int
    {
        $endpoints = WebhookEndpoint::query()->get();

        if ($endpoints->isEmpty()) {
            $this->info('No webhook endpoints configured.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'URL', 'Active', 'Events'],
            $endpoints->map(fn (WebhookEndpoint $endpoint) => [
                $endpoint->id,
                $endpoint->name,
                $endpoint->url,
                $endpoint->is_active ? 'yes' : 'no',
                implode(', ', $endpoint->events ?? []),
            ])->all()
        );

        return self::SUCCESS;
    }
}
