<?php

namespace App\Actions\Fortify;

use App\Services\ProgressiveLoginThrottle;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyAction;
use Laravel\Fortify\LoginRateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfTwoFactorAuthenticatable extends FortifyAction
{
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter, private ProgressiveLoginThrottle $throttle)
    {
        parent::__construct($guard, $limiter);
    }

    protected function throwFailedAuthenticationException($request): never
    {
        $this->throttle->recordFailure($request);
        throw ValidationException::withMessages(['email' => [trans('auth.failed')]]);
    }

    protected function twoFactorChallengeResponse($request, $user): Response
    {
        $this->throttle->clear($request);

        return parent::twoFactorChallengeResponse($request, $user);
    }
}
