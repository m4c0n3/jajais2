<?php

namespace Tests\Unit;

use App\Support\Updates\UpdateManifestVerifier;
use Firebase\JWT\JWT;
use Tests\TestCase;

class UpdateManifestVerifierTest extends TestCase
{
    private string $privateKey = <<<'KEY'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC2JiL+lZLk/PYm
CPE9G/GJhqKJINkZtlrSdAadyhppbwmfDhjgHzWeRvGMXxWUInmTZbGvrjBNM18y
b2uVNnidRLpxq81A4JUvfWxRxf/Gvtfi+O6Ti9IY8SXyNcXpshd+k7E+Ghu1+2xi
VTYIMNTzgPINJaUdLYIzzC6ItWFFamgVPv/oBmUjsFxvYR0nr3yjzGWtzk/Tupt5
u3bxUo26P5z9dJYKEwb4uUwnT6tVbxlaNkyD3h4yeGgTknSWI/nwsr273tCGarb4
M2C429mUvGAXcqzqauoto0zLH+k8Acvnoaceei1snA5qUz2PJq/SoqLopwuW9lzO
YSrtAJ8hAgMBAAECggEAIj+Qc+Lm0FR56dPjnBRdeXjNpc/90hZWBF0Zg2ClT663
leb+KWHk4IJBV53nPkevKVZFLx2m6lUe8Ko/hbpMunFaRxZBDRJNKrVAAS4j9hgb
GouovChdlB//fQe8U0EPptaZTG53SGPiUKjp9GdOGwzjNjKeVZqhcSDSnRTRmc3i
LEn4dBW0UUyhcIA6R1ASXyEUGjsoi8xpzWU9KmaEDZfG39wCFXoE/GYgzv1XNPe2
OMrZzGwTlURIo0AmtOufUXleNrIEMUossRDC+qV6DYxXVaCkdKMtZeEyEuvT5QCP
dSCGqEIxV5dYQcTyIF48NtdFKlJXhFank7ZfzcOuswKBgQD/5uce78TrtZnnjnqy
q+0KDMtfnWT5uT2W5NwaSXfXmQXVgRwQytEfv64ctxyI8BW2Krb+gO/CiEfLA/0u
SsChWyRQYV/u5DLHbTRWbKTcqSooPWjoEdG6C1yT3ypG3S3JECoIiSOSmb0aBKma
18pTV3zhzj5sIVRHXbr2TmcXywKBgQC2OAAsDKn22UG0hR99TgQfvCaXKSOBe4ol
RmThsiesZPn9gh+pFZSj9H6DcIdvbFd2OcovtJdU2paJpVrAlcbKgH88moehRten
gtJ/aIg0Jmh+d3NrIkDfa/vpmwMBvdqeTrn9XQfgNNI5BmUHQBgnu1Jg/v70RQwX
CDbcj9GPQwKBgQDb8pHKJFmEItWCkhLhySX20mJm7zhf75iBPnnz2hu4Yl0CUnNV
94zhFfZH1hcfmAOBTJt2ikoJUOssEmSlSjjhNx4yPE+hW+BNSo6GjeDxjDCg349w
kAhMOQG8Uz7aI7b7rvoB1iWOFrfTShig70KGeZbr2kIK3Ga85FIS6/ZuGwKBgHFO
gUzi0w1cKy9FWD9is83IAXcdbaamW1CyZSiyz1izsX+h2m4ZrUQGVGUHJDB0/i3V
4ZOoiDGLevA8kiIPAH4LSRM2RABVPOQ8xQfUyF0lRPTe8jY8JnrHeu3YJhZ/J+Io
sh6KKsuQDOjIwSmxAVQzdxY5efAKv2nTAcpE49phAoGBAJzn2ecAQ2lOnrL+4KW8
+ImXZISL5ILlS1Tm+NTtcAcRo9Fwo7cRNI0gek4lyFoBAceBNYzo3BXekqN1mxyQ
tRw1V9piYCwwrF28M4FT4Aqw3fKR7Vyb8oSgid6LTrvB5xVhl7SS5FFxlEd72jcT
Oxc17jlHz/rrU1etqleQcpg1
-----END PRIVATE KEY-----
KEY;

    private string $publicKey = <<<'KEY'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtiYi/pWS5Pz2JgjxPRvx
iYaiiSDZGbZa0nQGncoaaW8Jnw4Y4B81nkbxjF8VlCJ5k2Wxr64wTTNfMm9rlTZ4
nUS6cavNQOCVL31sUcX/xr7X4vjuk4vSGPEl8jXF6bIXfpOxPhobtftsYlU2CDDU
84DyDSWlHS2CM8wuiLVhRWpoFT7/6AZlI7Bcb2EdJ698o8xlrc5P07qbebt28VKN
uj+c/XSWChMG+LlMJ0+rVW8ZWjZMg94eMnhoE5J0liP58LK9u97Qhmq2+DNguNvZ
lLxgF3Ks6mrqLaNMyx/pPAHL56GnHnotbJwOalM9jyav0qKi6KcLlvZczmEq7QCf
IQIDAQAB
-----END PUBLIC KEY-----
KEY;

    public function test_verifies_signed_manifest(): void
    {
        config([
            'updates.jwt_public_key' => $this->publicKey,
            'updates.jwt_issuer' => 'updates.test',
            'updates.jwt_audience' => 'jajais',
        ]);

        $token = JWT::encode([
            'iss' => 'updates.test',
            'aud' => 'jajais',
            'exp' => time() + 3600,
            'updates' => [
                ['id' => 'update-1', 'type' => 'module', 'version' => '1.0.1'],
            ],
        ], $this->privateKey, 'RS256');

        $claims = app(UpdateManifestVerifier::class)->verify($token);

        $this->assertSame('updates.test', $claims['iss']);
        $this->assertSame('jajais', $claims['aud']);
    }

    public function test_rejects_invalid_issuer(): void
    {
        config([
            'updates.jwt_public_key' => $this->publicKey,
            'updates.jwt_issuer' => 'updates.test',
            'updates.jwt_audience' => 'jajais',
        ]);

        $token = JWT::encode([
            'iss' => 'wrong-issuer',
            'aud' => 'jajais',
            'exp' => time() + 3600,
        ], $this->privateKey, 'RS256');

        $this->expectExceptionMessage('Update JWT issuer mismatch.');

        app(UpdateManifestVerifier::class)->verify($token);
    }
}
