<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyLoginCodeRequest;
use App\Models\User;
use App\Services\LoginCodeService;
use App\Services\ProgressiveLoginThrottle;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoginCodeController extends Controller
{
    public function __construct(private StatefulGuard $guard, private LoginCodeService $codes, private ProgressiveLoginThrottle $throttle) {}

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', ['maskedEmail' => $this->mask($user->email)]);
    }

    public function store(VerifyLoginCodeRequest $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $this->throttle->ensureAllowed($request->merge(['email' => $user->email]));

        if (! $this->codes->verify($user, $request->validated('code'))) {
            $this->throttle->recordFailure($request);
            throw ValidationException::withMessages(['code' => 'El código es incorrecto o ya venció. Solicite uno nuevo.']);
        }

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');
        $this->guard->login($user, $remember);
        $request->session()->regenerate();
        $this->throttle->clear($request);

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $this->codes->canResend($user)) {
            return back()->withErrors(['code' => 'Espere un momento antes de solicitar otro código.']);
        }

        try {
            $this->codes->issue($user);
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar el código de verificación.', ['user_id' => $user->getKey(), 'error' => $exception->getMessage()]);

            return back()->withErrors(['code' => 'No se pudo enviar el correo. Intente de nuevo en unos minutos.']);
        }

        return back()->with('status', 'Le enviamos un nuevo código a su correo.');
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('login.id');

        return $id ? User::query()->where('status', 'active')->find($id) : null;
    }

    private function mask(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];

        return Str::substr($local, 0, 2).str_repeat('*', max(2, Str::length($local) - 2)).'@'.$domain;
    }
}
