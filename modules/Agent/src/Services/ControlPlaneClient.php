<?php

namespace Modules\Agent\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
        $retry = (int) config('agent.retry', 2);

        $request = Http::baseUrl($baseUrl)
            ->timeout($timeout)
            ->retry($retry, 200, null, false)
            ->acceptJson();

        if ($token) {
            $request = $request->withToken($token);
        }

        return $request->post($path, $payload);
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
