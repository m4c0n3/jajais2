<?php

namespace App\Console\Commands;

use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class WebhookRetry extends Command
{
    protected $signature = 'webhook:retry {delivery_id}';
    protected $description = 'Retry a failed webhook delivery';

    public function handle(): int
    {
        $delivery = WebhookDelivery::query()->find($this->argument('delivery_id'));

        if (!$delivery) {
            $this->error('Webhook delivery not found.');

            return self::FAILURE;
        }

        $delivery->update([
            'status' => 'pending',
            'next_attempt_at' => CarbonImmutable::now(),
        ]);

        SendWebhookDeliveryJob::dispatch($delivery->id);

        $this->info('Webhook delivery queued: '.$delivery->id);

        return self::SUCCESS;
    }
}
