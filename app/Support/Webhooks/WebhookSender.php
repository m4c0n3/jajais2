<?php

namespace App\Support\Webhooks;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebhookSender
{
    public function __construct(private WebhookSignature $signature)
    {
    }

    public function send(WebhookEndpoint $endpoint, WebhookDelivery $delivery): Response
    {
        $payload = json_encode($delivery->payload, JSON_UNESCAPED_SLASHES);
        $timestamp = CarbonImmutable::now()->timestamp;
        $signature = $this->signature->sign($endpoint->secret, $timestamp, $payload);

        $headers = array_merge($endpoint->headers ?? [], [
            'X-Webhook-Event' => $delivery->event,
            'X-Webhook-Id' => $delivery->correlation_id,
            'X-Webhook-Timestamp' => (string) $timestamp,
            'X-Webhook-Signature' => $signature,
        ]);

        return Http::timeout((int) $endpoint->timeout_seconds)
            ->acceptJson()
            ->withHeaders($headers)
            ->withBody($payload, 'application/json')
            ->post($endpoint->url);
    }

    public function shouldRetry(?Response $response, ?Throwable $exception): bool
    {
        if ($exception) {
            return true;
        }

        if (!$response) {
            return true;
        }

        $status = $response->status();

        return $status === 429 || $status >= 500;
    }
}
