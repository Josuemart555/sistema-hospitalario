<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_user_can_log_in_and_session_is_regenerated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Secure!Password123'), 'status' => 'active']);
        $sessionId = 'known-session-id';

        $response = $this->withSession(['marker' => true])->post('/login', ['email' => $user->email, 'password' => 'Secure!Password123']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
    }

    public function test_suspended_user_receives_generic_login_error(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Secure!Password123'), 'status' => 'suspended']);

        $response = $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'Secure!Password123']);

        $response->assertRedirect('/login')->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $this->assertGuest();
    }

    public function test_login_is_temporarily_blocked_after_five_failures(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Secure!Password123')]);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $response = $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'Secure!Password123']);

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_recovery_does_not_reveal_if_account_exists(): void
    {
        $known = User::factory()->create();

        $knownResponse = $this->from('/forgot-password')->post('/forgot-password', ['email' => $known->email]);
        $unknownResponse = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.test']);

        $knownResponse->assertRedirect('/forgot-password')->assertSessionHas('status', trans('passwords.sent'));
        $unknownResponse->assertRedirect('/forgot-password')->assertSessionHas('status', trans('passwords.sent'));
    }
}
