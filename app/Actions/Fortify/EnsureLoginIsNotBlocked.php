<?php

namespace App\Actions\Fortify;

use App\Services\ProgressiveLoginThrottle;
use Closure;
use Illuminate\Http\Request;

class EnsureLoginIsNotBlocked
{
    public function __construct(private ProgressiveLoginThrottle $throttle) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $this->throttle->ensureAllowed($request);

        return $next($request);
    }
}
