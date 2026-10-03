<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_cannot_enable_two_factor_without_administrator_permission(): void
    {
        $user = User::factory()->create(['two_factor_allowed' => false]);

        $response = $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])->from('/perfil')->post('/user/two-factor-authentication');

        $response->assertRedirect('/perfil')->assertSessionHasErrors('two_factor');
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_authorized_user_can_begin_two_factor_setup(): void
    {
        $user = User::factory()->create(['two_factor_allowed' => true]);

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])->post('/user/two-factor-authentication');

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }
}
