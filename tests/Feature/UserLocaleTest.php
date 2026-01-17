<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_locale_is_applied(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->get('/health')->assertOk();

        $this->assertSame('en', app()->getLocale());
    }
}
