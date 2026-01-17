<?php

namespace Tests\Unit;

use App\Support\Webhooks\WebhookSignature;
use PHPUnit\Framework\TestCase;

class WebhookSignatureTest extends TestCase
{
    public function test_signature_is_stable(): void
    {
        $signature = new WebhookSignature();
        $secret = 'secret';
        $timestamp = 1700000000;
        $payload = '{"hello":"world"}';

        $expected = 'v1='.hash_hmac('sha256', 'v1:1700000000:{"hello":"world"}', $secret);

        $this->assertSame($expected, $signature->sign($secret, $timestamp, $payload));
    }
}
