<?php

namespace Tests\Unit;

use App\Support\Licensing\LicenseService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class LicenseServiceTest extends TestCase
{
    public function test_parse_token_extracts_dates(): void
    {
        $service = new LicenseService();
        $token = '{"modules":["alpha"],"valid_to":"2030-01-01T00:00:00Z","grace_to":"2030-01-02T00:00:00Z"}';

        $parsed = $service->parseToken($token);
        [$validTo, $graceTo] = $service->extractDates($parsed);

        $this->assertIsArray($parsed);
        $this->assertSame(['alpha'], $parsed['modules']);
        $this->assertInstanceOf(CarbonImmutable::class, $validTo);
        $this->assertInstanceOf(CarbonImmutable::class, $graceTo);
    }
}
