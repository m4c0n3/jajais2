<?php

namespace Tests\Feature;

use App\Support\System\ModuleSetResolver;
use Tests\TestCase;

class ModuleSetResolverTest extends TestCase
{
    public function test_resolves_control_plane_set(): void
    {
        $resolver = app(ModuleSetResolver::class);

        $set = $resolver->resolve('control-plane');

        $this->assertContains('ControlPlaneCore', $set);
    }
}
