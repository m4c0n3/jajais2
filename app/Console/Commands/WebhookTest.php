<?php

namespace App\Console\Commands;

use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class WebhookTest extends Command
{
    protected $signature = 'webhook:test {endpoint_id} {--event=module.enabled}';
    protected $description = 'Send a test webhook delivery';

    public function handle(): int
    {
        $endpoint = WebhookEndpoint::query()->find($this->argument('endpoint_id'));

        if (!$endpoint) {
            $this->error('Webhook endpoint not found.');

            return self::FAILURE;
        }

        $event = (string) $this->option('event');
        $payload = [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'occurred_at' => CarbonImmutable::now()->toIso8601String(),
            'actor' => ['type' => 'system', 'id' => null],
            'data' => ['test' => true],
        ];

        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event' => $event,
            'payload' => $payload,
            'status' => 'pending',
            'attempt' => 0,
            'correlation_id' => $payload['id'],
        ]);

        SendWebhookDeliveryJob::dispatch($delivery->id);

        $this->info('Test delivery queued: '.$delivery->id);

        return self::SUCCESS;
    }
}
