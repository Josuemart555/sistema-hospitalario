<?php

namespace App\Actions\Fortify;

use App\Services\LoginCodeService;
use App\Services\ProgressiveLoginThrottle;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AttemptToAuthenticate
{
    public function __construct(private StatefulGuard $guard, private ProgressiveLoginThrottle $throttle, private LoginCodeService $codes) {}

    public function handle(Request $request, callable $next): mixed
    {
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);
        if (! $user) {
            $this->throttle->recordFailure($request);
            throw ValidationException::withMessages(['email' => [trans('auth.failed')]]);
        }
        $this->throttle->clear($request);

        if ($user->two_factor_allowed) {
            return $this->startEmailChallenge($request, $user);
        }

        $this->guard->login($user, $request->boolean('remember'));

        return $next($request);
    }

    private function startEmailChallenge(Request $request, $user): mixed
    {
        try {
            $this->codes->issue($user);
        } catch (\Throwable $exception) {
            Log::error('No se pudo enviar el código de verificación.', ['user_id' => $user->getKey(), 'error' => $exception->getMessage()]);
            throw ValidationException::withMessages(['email' => 'No se pudo enviar el código de verificación. Intente de nuevo en unos minutos.']);
        }

        $request->session()->put(['login.id' => $user->getKey(), 'login.remember' => $request->boolean('remember')]);

        return redirect()->route('login.code');
    }
}
