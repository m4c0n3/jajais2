<?php

namespace App\Support\Webhooks;

use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Support\Observability\RequestContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    public function dispatch(string $event, array $data, ?array $actor = null): int
    {
        $endpoints = WebhookEndpoint::query()
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        if ($endpoints->isEmpty()) {
            return 0;
        }

        $payload = [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'occurred_at' => CarbonImmutable::now()->toIso8601String(),
            'actor' => $actor ?? ['type' => 'system', 'id' => null],
            'data' => $data,
        ];

        $count = 0;

        foreach ($endpoints as $endpoint) {
            $delivery = WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event' => $event,
                'payload' => $payload,
                'status' => 'pending',
                'attempt' => 0,
                'correlation_id' => $payload['id'],
            ]);

            SendWebhookDeliveryJob::dispatch($delivery->id, RequestContext::currentRequestId());
            $count++;
        }

        return $count;
    }
}
