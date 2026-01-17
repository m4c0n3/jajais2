<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentLicenseRefreshInvalidTest extends TestCase
{
    use RefreshDatabase;

    private const PRIVATE_KEY = <<<PEM
-----BEGIN PRIVATE KEY-----
MIIEuwIBADANBgkqhkiG9w0BAQEFAASCBKUwggShAgEAAoIBAQCeMo4eaSvAeRnG
huwJIfq9nxBRbDxbvx30JDcpX2hkAx7JqCNwUQGfGfGXcWGA2FeAwYfDJZPqylFw
/VdIREkf12VbaAuvfXtBp3fyWHEIcAbkdE3lC6DxTs1bwvyc6r1ldmVMO86NvfNu
ILZFQir3NL8hMnalBNrqYcJMvE3nF1R+RdqivFOu4byeWWIfRSQtrUk5OSU0xli9
eq/1Szh+bo0/cz+5yitHfH/N3uJu1/Q9kgyTSiaqkm2kiSff8dkRHWWrZ7rhQ4eB
gJUqbftk/imsMVCQTBjz8rncyIDhKyE1IpCXkpIc0MDhf08bRRj3RmaAIspo6yLF
lg54pLFZAgMBAAECgf8TqnJjmMFiqym7wjGfPiERwjQTgDkv7tbbouax5Ul9e7Ba
75++Gbu1Xx0zZF72fZohUZdJS0Bojrp59AmVuXM9z3Qxvtr+Bg0zDYxER97Ok8EK
7t2EctMm9tZ3XJRZTQmQGllc/wwlYdun0b4JRFzxkymknBnmgd9xGvxuZQOCuvnk
uZklPkaK2BmHSKspNbGDY4VzpgUkXCPcUEcF8hnEFbP6h7XBN+YO+g24s67OkaBK
DWFCAbk1TA6HPYhe9beC3GPn9KzVN7xzt2SyfC0YfKSNMW0CoqjvgjqwKWRSwmf7
awfzDGsB0Mx+jJFFIO/apSVHYSJNVqs9v183k+ECgYEA0DvGy6DgMksUWWRf2uol
eYbaYTYfPao99mEuXnf7KA5rg7xEWgJpGiRtq/sP+9QwpaLyz377eqgHPcRFXhqy
NvCWlM/nMGZyvGPnn5EtEla7F9egc/YVLtKRb78iHbwxxtgBzgI+yjz0iZD9Q8vY
DaFTaIBzBKouV5wq6NAZkykCgYEAwnx7kmdKlNjkrQ+Og0QGtebIYzWgZPEC3TTk
613oz78ZaHM69vhExLxLGt5QPWEzNpx/kyKgSBZX4t3A7VtL/rTphyTpgPYymgCK
aey1Zf7fiATwms6XOOs+cjsy4UZGoeSN9ATo7fXGNda8IkPLCYUiZTjqurbTSRhB
+cnmorECgYANyVdhFfah/cyMGpQqF0SB5kbBFuc8mu/dRxPd102+mi3OHAHef7hb
rbvBHi8xuhu6a65txHd76HIKSdtZ3qSb9JPTqGwjDTVdebPVIbR9OVbLvk/2PX2r
iu9sGZh1pYcaJiUAca+cjiqWjQ3nljBovpyaF58F2QqWbFV+8oAu+QKBgDVvKixh
QLaAmOOLgKZEDGvxymCnnTfel+Da5YJdPNfHM13lOvAb6hj7es8ZAYa7q+x3Nv3f
55WmveLQ9m7ARLLoVbkRxS3vdpulRmIv7O7nBddDNC/0TswOpguQhDwsqL9WIkJH
DxBCFIE6TFpFsgUdlQOmjadbD9XnWkkc1cchAoGBALcxUwtC38dlE2QoYlMuQ55d
8XbhCp7+Wo2ZZ5I9lqRX3iY27Nt3n+7iedc13B2AAB+GE3bz9alIL/G3IHe1J7kt
oqfth2p7UVdJUVexQbxlG8FtZsK6Xh+194zXUmKmh4YLvLynzPn9fMNcJw2wiJAq
nSiO09qSbb1VOrm96Cj2
-----END PRIVATE KEY-----
PEM;

    private const PUBLIC_KEY = <<<PEM
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnjKOHmkrwHkZxobsCSH6
vZ8QUWw8W78d9CQ3KV9oZAMeyagjcFEBnxnxl3FhgNhXgMGHwyWT6spRcP1XSERJ
H9dlW2gLr317Qad38lhxCHAG5HRN5Qug8U7NW8L8nOq9ZXZlTDvOjb3zbiC2RUIq
9zS/ITJ2pQTa6mHCTLxN5xdUfkXaorxTruG8nlliH0UkLa1JOTklNMZYvXqv9Us4
fm6NP3M/ucorR3x/zd7ibtf0PZIMk0omqpJtpIkn3/HZER1lq2e64UOHgYCVKm37
ZP4prDFQkEwY8/K53MiA4SshNSKQl5KSHNDA4X9PG0UY90ZmgCLKaOsixZYOeKSx
WQIDAQAB
-----END PUBLIC KEY-----
PEM;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createInstanceStateTable();
        $this->createLicenseTokensTable();
        $this->createAuditLogsTable();

        config()->set('agent.enabled', true);
        config()->set('agent.base_url', 'https://control-plane.test');
        config()->set('agent.token', 'token');
        config()->set('agent.jwt_public_key', self::PUBLIC_KEY);
        config()->set('agent.jwt_issuer', 'control-plane');
        config()->set('agent.jwt_audience', 'instance-uuid');
    }

    public function test_invalid_token_is_not_activated(): void
    {
        DB::table('instance_state')->insert([
            'instance_uuid' => 'instance-uuid',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        DB::table('license_tokens')->insert([
            'fetched_at' => CarbonImmutable::now(),
            'valid_to' => CarbonImmutable::now()->addDay(),
            'grace_to' => CarbonImmutable::now()->addDays(2),
            'token' => 'active-token',
            'parsed' => json_encode(['modules' => ['HelloWorld']]),
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $jwt = JWT::encode([
            'iss' => 'wrong-issuer',
            'aud' => 'instance-uuid',
            'exp' => time() + 60,
            'nbf' => time() - 10,
        ], self::PRIVATE_KEY, 'RS256');

        Http::fake([
            'https://control-plane.test/*' => Http::response([
                'token' => $jwt,
                'valid_to' => '2030-01-01T00:00:00Z',
            ], 200),
        ]);

        $this->artisan('agent:license-refresh')->assertExitCode(1);

        $this->assertSame(1, DB::table('license_tokens')->whereNull('revoked_at')->count());
        $this->assertSame(1, DB::table('license_tokens')->whereNotNull('revoked_at')->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'license.token_invalid',
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

    private function createAuditLogsTable(): void
    {
        if (Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
}
