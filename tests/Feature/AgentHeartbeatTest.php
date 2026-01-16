<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createInstanceStateTable();
        config()->set('agent.enabled', true);
        config()->set('agent.base_url', 'https://control-plane.test');
        config()->set('agent.token', 'token');
    }

    public function test_heartbeat_sends_payload_and_updates_state(): void
    {
        Http::fake([
            'https://control-plane.test/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->artisan('agent:heartbeat')->assertExitCode(0);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://control-plane.test/api/v1/instances/'.$payload['instance_uuid'].'/heartbeat'
                && isset($payload['app_env'], $payload['php_version'], $payload['timestamp']);
        });

        $this->assertDatabaseCount('instance_state', 1);
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
