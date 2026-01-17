<?php

namespace Tests\Feature;

use App\Jobs\SendWebhookDeliveryJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Support\Webhooks\WebhookDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_sends_delivery(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'n8n',
            'url' => 'https://example.test/webhook',
            'is_active' => true,
            'secret' => 'secret',
            'events' => ['module.enabled'],
            'headers' => ['X-Test' => '1'],
            'timeout_seconds' => 5,
            'max_attempts' => 2,
        ]);

        Http::fake([
            'https://example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $dispatcher = app(WebhookDispatcher::class);
        $dispatcher->dispatch('module.enabled', ['id' => 'HelloWorld']);

        $delivery = WebhookDelivery::first();
        $this->assertNotNull($delivery);

        $job = new SendWebhookDeliveryJob($delivery->id);
        $job->handle(app(\App\Support\Webhooks\WebhookSender::class));

        $delivery->refresh();
        $this->assertSame('delivered', $delivery->status);
        $this->assertNotNull($delivery->delivered_at);
        $this->assertNotNull($endpoint->fresh()->last_success_at);
    }
}
