<?php

namespace App\Support\Webhooks;

class WebhookSignature
{
    public function sign(string $secret, int $timestamp, string $payload): string
    {
        $base = $this->baseString($timestamp, $payload);

        return 'v1='.hash_hmac('sha256', $base, $secret);
    }

    public function baseString(int $timestamp, string $payload): string
    {
        return 'v1:'.$timestamp.':'.$payload;
    }
}
