<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Support\Webhooks\WebhookSender;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWebhookDeliveryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $deliveryId, public ?string $requestId = null)
    {
    }

    public function handle(WebhookSender $sender): void
    {
        if ($this->requestId) {
            Log::withContext(['request_id' => $this->requestId]);
        }

        $delivery = WebhookDelivery::with('endpoint')->find($this->deliveryId);

        if (!$delivery || !$delivery->endpoint || $delivery->status === 'delivered') {
            return;
        }

        $endpoint = $delivery->endpoint;
        $delivery->update([
            'status' => 'sending',
            'attempt' => $delivery->attempt + 1,
        ]);

        $exception = null;
        $response = null;

        try {
            $response = $sender->send($endpoint, $delivery);
        } catch (Throwable $throwable) {
            $exception = $throwable;
        }

        if ($response && $response->successful()) {
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => CarbonImmutable::now(),
                'last_response_code' => $response->status(),
                'last_error' => null,
            ]);

            $endpoint->update([
                'last_success_at' => CarbonImmutable::now(),
                'last_failure_reason' => null,
            ]);

            return;
        }

        $statusCode = $response?->status();
        $retryable = $sender->shouldRetry($response, $exception);

        if (!$retryable || $delivery->attempt >= $endpoint->max_attempts) {
            $delivery->update([
                'status' => 'failed',
                'last_response_code' => $statusCode,
                'last_error' => $exception?->getMessage(),
                'next_attempt_at' => null,
            ]);

            $endpoint->update([
                'last_failure_at' => CarbonImmutable::now(),
                'last_failure_reason' => $exception?->getMessage() ?? ($statusCode ? "HTTP {$statusCode}" : 'unknown'),
            ]);

            return;
        }

        $delay = $this->calculateDelay($endpoint, $delivery->attempt);

        $delivery->update([
            'status' => 'pending',
            'last_response_code' => $statusCode,
            'last_error' => $exception?->getMessage(),
            'next_attempt_at' => CarbonImmutable::now()->addSeconds($delay),
        ]);

        $this->release($delay);
    }

    private function calculateDelay($endpoint, int $attempt): int
    {
        $backoff = $endpoint->backoff_seconds ?? [];

        if (is_array($backoff) && isset($backoff[$attempt - 1])) {
            return (int) $backoff[$attempt - 1];
        }

        return min(300, (int) pow(2, $attempt));
    }
}
