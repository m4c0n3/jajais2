<?php

namespace App\Support\Observability;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Log;

class RequestContextJobMiddleware
{
    public function handle(Job $job, callable $next): mixed
    {
        $payload = $job->payload();
        $requestId = $payload['request_id'] ?? null;

        if (is_string($requestId) && $requestId !== '') {
            Log::withContext(['request_id' => $requestId]);
        }

        return $next($job);
    }
}
