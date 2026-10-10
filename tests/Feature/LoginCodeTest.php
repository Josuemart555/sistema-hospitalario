<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LoginCodeNotification;
use App\Services\LoginCodeService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginCodeTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function protectedUser(): User
    {
        return User::factory()->create([
            'password' => Hash::make('Secure!Password123'), 'status' => 'active', 'two_factor_allowed' => true,
        ]);
    }

    /** Inicia sesión con contraseña y devuelve el código enviado por correo. */
    private function startLogin(User $user): string
    {
        Notification::fake();
        $this->post('/login', ['email' => $user->email, 'password' => 'Secure!Password123'])->assertRedirect(route('login.code'));
        $code = null;
        Notification::assertSentTo($user, LoginCodeNotification::class, function (LoginCodeNotification $notification) use ($user, &$code): bool {
            $code = $this->extractCode($notification->toMail($user)->render());

            return true;
        });

        return $code;
    }

    private function extractCode(string $html): string
    {
        preg_match('/class="code"[^>]*>\s*(\d{6})\s*</', (string) $html, $matches);

        return $matches[1];
    }

    public function test_user_with_email_code_is_not_logged_in_until_code_is_verified(): void
    {
        $user = $this->protectedUser();

        $this->startLogin($user);

        $this->assertGuest();
        $this->get(route('login.code'))->assertOk()->assertSee('Código de 6 dígitos');
    }

    public function test_correct_code_completes_login(): void
    {
        $user = $this->protectedUser();
        $code = $this->startLogin($user);

        $this->post(route('login.code.store'), ['code' => $code])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_code_cannot_be_reused(): void
    {
        $user = $this->protectedUser();
        $code = $this->startLogin($user);
        $this->post(route('login.code.store'), ['code' => $code]);
        $this->post('/logout');

        $this->withSession(['login.id' => $user->id])->from(route('login.code'))->post(route('login.code.store'), ['code' => $code])->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_wrong_code_is_rejected_and_invalidated_after_five_attempts(): void
    {
        $user = $this->protectedUser();
        $code = $this->startLogin($user);

        for ($i = 0; $i < LoginCodeService::MAX_ATTEMPTS; $i++) {
            $this->from(route('login.code'))->post(route('login.code.store'), ['code' => $code === '000000' ? '111111' : '000000'])->assertSessionHasErrors('code');
        }
        // Tras 5 fallos el límite de intentos (throttle:two-factor) bloquea, incluso con el código correcto.
        $this->from(route('login.code'))->post(route('login.code.store'), ['code' => $code])->assertStatus(429);

        $this->assertGuest();
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = $this->protectedUser();
        $code = $this->startLogin($user);
        Cache::forget('login.otp.'.$user->id);

        $this->from(route('login.code'))->post(route('login.code.store'), ['code' => $code])->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_resend_respects_cooldown(): void
    {
        $user = $this->protectedUser();
        $this->startLogin($user);

        $this->from(route('login.code'))->post(route('login.code.resend'))->assertSessionHasErrors('code');
        Cache::forget('login.otp.cooldown.'.$user->id);
        $this->from(route('login.code'))->post(route('login.code.resend'))->assertSessionHas('status');
    }

    public function test_code_page_requires_pending_login(): void
    {
        $this->get(route('login.code'))->assertRedirect(route('login'));
    }

    public function test_user_without_requirement_logs_in_directly(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Secure!Password123'), 'status' => 'active', 'two_factor_allowed' => false]);
        Notification::fake();

        $this->post('/login', ['email' => $user->email, 'password' => 'Secure!Password123'])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();
    }

    public function test_code_email_is_branded_and_contains_code(): void
    {
        $user = User::factory()->make(['name' => 'Ana']);

        $html = (string) (new LoginCodeNotification('123456', 10))->toMail($user)->render();

        $this->assertStringContainsString('123456', $html);
        $this->assertStringContainsString(config('app.name'), $html);
        $this->assertStringContainsString('10 minutos', $html);
    }
}
