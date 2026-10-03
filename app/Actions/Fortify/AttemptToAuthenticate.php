<?php

namespace App\Actions\Fortify;

use App\Services\ProgressiveLoginThrottle;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AttemptToAuthenticate
{
    public function __construct(private StatefulGuard $guard, private ProgressiveLoginThrottle $throttle) {}

    public function handle(Request $request, callable $next): mixed
    {
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);
        if (! $user) {
            $this->throttle->recordFailure($request);
            throw ValidationException::withMessages(['email' => [trans('auth.failed')]]);
        }
        $this->guard->login($user, $request->boolean('remember'));
        $this->throttle->clear($request);

        return $next($request);
    }
}
