<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgressiveLoginThrottle
{
    public function ensureAllowed(Request $request): void
    {
        $seconds = max(0, (int) Cache::get($this->blockKey($request), 0) - now()->timestamp);
        if ($seconds > 0) {
            Log::notice('Inicio de sesión temporalmente bloqueado.', ['identity_hash' => $this->key($request), 'ip' => $request->ip(), 'retry_after' => $seconds]);
            throw ValidationException::withMessages(['email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => max(1, (int) ceil($seconds / 60))])]);
        }
    }

    public function recordFailure(Request $request): void
    {
        $key = $this->failureKey($request);
        $failures = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $failures, now()->addDay());
        Log::warning('Intento de inicio de sesión fallido.', ['identity_hash' => $this->key($request), 'ip' => $request->ip(), 'failures' => $failures]);
        $minutes = match (true) {
            $failures >= 20 => 60, $failures >= 15 => 15, $failures >= 10 => 5, $failures >= 5 => 1, default => 0
        };
        if ($minutes > 0) {
            Cache::put($this->blockKey($request), now()->addMinutes($minutes)->timestamp, now()->addDay());
        }
    }

    public function clear(Request $request): void
    {
        Cache::forget($this->failureKey($request));
        Cache::forget($this->blockKey($request));
    }

    private function key(Request $request): string
    {
        return hash('sha256', Str::lower((string) $request->input('email')).'|'.$request->ip());
    }

    private function failureKey(Request $request): string
    {
        return 'login.failures.'.$this->key($request);
    }

    private function blockKey(Request $request): string
    {
        return 'login.blocked.'.$this->key($request);
    }
}
