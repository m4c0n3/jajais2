<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class RequestIdMiddlewareTest extends TestCase
{
    public function test_request_id_header_is_added(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertHeader('X-Request-Id');
    }

    public function test_request_id_is_logged_in_context(): void
    {
        Log::spy();

        $response = $this->get('/health');

        $response->assertOk();

        Log::shouldHaveReceived('withContext')
            ->with(Mockery::on(function ($context): bool {
                return is_array($context)
                    && isset($context['request_id'])
                    && is_string($context['request_id'])
                    && $context['request_id'] !== '';
            }))
            ->atLeast()
            ->once();
    }
}
