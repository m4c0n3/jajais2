<?php

namespace Tests\Feature;

use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_on_rate_limit_then_success(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'n8n',
            'url' => 'https://example.test/webhook',
            'is_active' => true,
            'secret' => 'secret',
            'events' => ['module.enabled'],
            'timeout_seconds' => 5,
            'max_attempts' => 3,
            'backoff_seconds' => [1, 1],
        ]);

        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event' => 'module.enabled',
            'payload' => ['id' => 'corr', 'event' => 'module.enabled'],
            'status' => 'pending',
            'attempt' => 0,
            'correlation_id' => 'corr',
        ]);

        Http::fakeSequence()
            ->push(['error' => 'rate limit'], 429)
            ->push(['ok' => true], 200);

        $job = new SendWebhookDeliveryJob($delivery->id);
        $job->handle(app(\App\Support\Webhooks\WebhookSender::class));

        $delivery->refresh();
        $this->assertSame('pending', $delivery->status);
        $this->assertSame(1, $delivery->attempt);

        $job->handle(app(\App\Support\Webhooks\WebhookSender::class));
        $delivery->refresh();

        $this->assertSame('delivered', $delivery->status);
        $this->assertSame(2, $delivery->attempt);
    }
}
