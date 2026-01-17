<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentHeartbeatRetryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createInstanceStateTable();

        DB::table('instance_state')->insert([
            'instance_uuid' => 'instance-uuid',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        config()->set('agent.enabled', true);
        config()->set('agent.base_url', 'https://control-plane.test');
        config()->set('agent.token', 'token');
        config()->set('agent.retry', 2);
        config()->set('agent.retry_backoff_ms', 1);
        config()->set('agent.retry_max_seconds', 5);
    }

    public function test_heartbeat_retries_on_rate_limit(): void
    {
        Http::fakeSequence()
            ->push(['error' => 'rate limit'], 429)
            ->push(['status' => 'ok'], 200);

        $this->artisan('agent:heartbeat')->assertExitCode(0);

        Http::assertSentCount(2);
        $this->assertNotNull(DB::table('instance_state')->value('last_heartbeat_at'));
    }

    private function createInstanceStateTable(): void
    {
        if (Schema::hasTable('instance_state')) {
            return;
        }

        Schema::create('instance_state', function (Blueprint $table): void {
            $table->id();
            $table->uuid('instance_uuid')->unique();
            $table->dateTime('registered_at')->nullable();
            $table->dateTime('last_heartbeat_at')->nullable();
            $table->dateTime('last_license_refresh_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }
}
