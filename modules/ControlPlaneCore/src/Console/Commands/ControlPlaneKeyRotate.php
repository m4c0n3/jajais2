<?php

namespace Modules\ControlPlaneCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Modules\ControlPlaneCore\Models\SigningKey;

class ControlPlaneKeyRotate extends Command
{
    protected $signature = 'control-plane:key:rotate';
    protected $description = 'Rotate signing keys for control plane tokens.';

    public function handle(): int
    {
        $keyPair = $this->generateKeyPair();
        $kid = Str::uuid()->toString();

        SigningKey::query()->update(['active' => false]);

        SigningKey::create([
            'kid' => $kid,
            'public_key' => $keyPair['public'],
            'private_key_encrypted' => Crypt::encryptString($keyPair['private']),
            'active' => true,
        ]);

        $this->info('New signing key created: '.$kid);

        return Command::SUCCESS;
    }

    private function generateKeyPair(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$resource) {
            throw new \RuntimeException('Failed to generate key pair.');
        }

        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);

        if (!$details || !isset($details['key'])) {
            throw new \RuntimeException('Failed to read public key.');
        }

        return [
            'private' => $privateKey,
            'public' => $details['key'],
        ];
    }
}
