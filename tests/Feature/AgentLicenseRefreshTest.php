<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentLicenseRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createInstanceStateTable();
        $this->createLicenseTokensTable();

        config()->set('agent.enabled', true);
        config()->set('agent.base_url', 'https://control-plane.test');
        config()->set('agent.token', 'token');
    }

    public function test_license_refresh_stores_token(): void
    {
        Http::fake([
            'https://control-plane.test/*' => Http::response([
                'token' => 'dummy-token',
                'valid_to' => '2030-01-01T00:00:00Z',
                'grace_to' => '2030-01-02T00:00:00Z',
                'parsed' => [
                    'modules' => ['agent'],
                    'valid_to' => '2030-01-01T00:00:00Z',
                    'grace_to' => '2030-01-02T00:00:00Z',
                ],
            ], 200),
        ]);

        $this->artisan('agent:license-refresh')->assertExitCode(0);

        $this->assertDatabaseCount('license_tokens', 1);
        $this->assertDatabaseHas('license_tokens', [
            'token' => 'dummy-token',
        ]);
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

    private function createLicenseTokensTable(): void
    {
        if (Schema::hasTable('license_tokens')) {
            return;
        }

        Schema::create('license_tokens', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('fetched_at')->nullable();
            $table->dateTime('valid_to');
            $table->dateTime('grace_to')->nullable();
            $table->longText('token');
            $table->json('parsed')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->string('last_refresh_status')->nullable();
            $table->text('last_refresh_error')->nullable();
            $table->timestamps();
        });
    }
}
