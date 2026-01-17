<?php

namespace Modules\Agent\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class ControlPlaneClient
{
    public function register(array $payload): Response
    {
        return $this->post('/api/v1/instances/register', $payload, $this->getRegistrationToken());
    }

    public function heartbeat(string $instanceUuid, array $payload): Response
    {
        return $this->post("/api/v1/instances/{$instanceUuid}/heartbeat", $payload, $this->getToken());
    }

    public function refreshLicense(string $instanceUuid, array $payload): Response
    {
        return $this->post("/api/v1/instances/{$instanceUuid}/license/refresh", $payload, $this->getToken());
    }

    private function post(string $path, array $payload, ?string $token): Response
    {
        $baseUrl = rtrim((string) config('agent.base_url'), '/');

        if ($baseUrl === '') {
            throw new \RuntimeException('CONTROL_PLANE_URL is not configured.');
        }

        $timeout = (int) config('agent.timeout', 5);
        $request = Http::baseUrl($baseUrl)->timeout($timeout)->acceptJson();

        if ($token) {
            $request = $request->withToken($token);
        }

        return $this->sendWithRetry($request, $path, $payload);
    }

    private function sendWithRetry($request, string $path, array $payload): Response
    {
        $retries = (int) config('agent.retry', 2);
        $baseBackoffMs = (int) config('agent.retry_backoff_ms', 200);
        $maxRetrySeconds = (int) config('agent.retry_max_seconds', 10);
        $start = microtime(true);
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $response = $request->post($path, $payload);
            } catch (Throwable $exception) {
                if ($this->shouldRetryException($exception, $attempt, $retries, $start, $maxRetrySeconds)) {
                    $this->sleepWithBackoff($attempt, $baseBackoffMs);
                    continue;
                }

                throw $exception;
            }

            if ($this->shouldRetryResponse($response, $attempt, $retries, $start, $maxRetrySeconds)) {
                $this->sleepWithBackoff($attempt, $baseBackoffMs);
                continue;
            }

            return $response;
        }
    }

    private function shouldRetryResponse(Response $response, int $attempt, int $retries, float $start, int $maxRetrySeconds): bool
    {
        $status = $response->status();

        if ($status === 429 || $status >= 500) {
            return $this->canRetry($attempt, $retries, $start, $maxRetrySeconds);
        }

        return false;
    }

    private function shouldRetryException(Throwable $exception, int $attempt, int $retries, float $start, int $maxRetrySeconds): bool
    {
        return $this->canRetry($attempt, $retries, $start, $maxRetrySeconds);
    }

    private function canRetry(int $attempt, int $retries, float $start, int $maxRetrySeconds): bool
    {
        if ($attempt > $retries) {
            return false;
        }

        if ((microtime(true) - $start) > $maxRetrySeconds) {
            return false;
        }

        return true;
    }

    private function sleepWithBackoff(int $attempt, int $baseBackoffMs): void
    {
        $sleepMs = $baseBackoffMs * (2 ** max(0, $attempt - 1));
        usleep($sleepMs * 1000);
    }

    private function getToken(): ?string
    {
        $token = config('agent.token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function getRegistrationToken(): ?string
    {
        $token = config('agent.registration_token');

        return is_string($token) && $token !== '' ? $token : null;
    }
}
