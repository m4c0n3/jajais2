<?php

namespace Tests\Unit;

use App\Support\Licensing\JwtTokenVerifier;
use Firebase\JWT\JWT;
use Tests\TestCase;

class JwtTokenVerifierTest extends TestCase
{
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

    public function test_verifies_valid_token(): void
    {
        config()->set('agent.jwt_public_key', self::PUBLIC_KEY);
        config()->set('agent.jwt_issuer', 'control-plane');
        config()->set('agent.jwt_audience', 'instance-1');

        $payload = [
            'iss' => 'control-plane',
            'aud' => 'instance-1',
            'exp' => time() + 60,
            'nbf' => time() - 10,
            'modules' => ['hello'],
        ];

        $jwt = JWT::encode($payload, self::PRIVATE_KEY, 'RS256');
        $claims = (new JwtTokenVerifier())->verify($jwt);

        $this->assertSame('control-plane', $claims['iss']);
        $this->assertSame('instance-1', $claims['aud']);
        $this->assertSame(['hello'], $claims['modules']);
    }

    public function test_rejects_invalid_audience(): void
    {
        config()->set('agent.jwt_public_key', self::PUBLIC_KEY);
        config()->set('agent.jwt_issuer', 'control-plane');
        config()->set('agent.jwt_audience', 'instance-1');

        $payload = [
            'iss' => 'control-plane',
            'aud' => 'other-instance',
            'exp' => time() + 60,
            'nbf' => time() - 10,
        ];

        $jwt = JWT::encode($payload, self::PRIVATE_KEY, 'RS256');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT audience mismatch.');

        (new JwtTokenVerifier())->verify($jwt);
    }
}
