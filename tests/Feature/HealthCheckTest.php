<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_basic_payload(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'version',
            'time',
        ]);
    }
}
