<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstallGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_page_is_available_before_init(): void
    {
        $this->get('/install')->assertOk();
    }

    public function test_install_page_is_blocked_after_init(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'app.locked',
            'value' => 'true',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/install')->assertNotFound();
    }
}
