<?php

namespace Tests\Unit;

use App\Support\Modules\ModuleManifest;
use PHPUnit\Framework\TestCase;

class ModuleManifestTest extends TestCase
{
    public function test_manifest_parses_required_fields(): void
    {
        $manifest = ModuleManifest::fromArray([
            'id' => 'HelloWorld',
            'name' => 'HelloWorld',
            'version' => '1.0.0',
            'license_required' => true,
            'permissions' => ['hello.view'],
        ]);

        $this->assertSame('HelloWorld', $manifest->id);
        $this->assertSame('1.0.0', $manifest->version);
        $this->assertTrue($manifest->licenseRequired);
        $this->assertSame(['hello.view'], $manifest->permissions);
    }
}
