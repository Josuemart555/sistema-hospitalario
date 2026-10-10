<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\LoginCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginCodeService
{
    public const TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    public function issue(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->key($user), ['hash' => Hash::make($code), 'attempts' => 0], now()->addMinutes(self::TTL_MINUTES));
        Cache::put($this->cooldownKey($user), true, now()->addSeconds(self::RESEND_SECONDS));
        $user->notify(new LoginCodeNotification($code, self::TTL_MINUTES));
    }

    public function verify(User $user, string $code): bool
    {
        $entry = Cache::get($this->key($user));
        if (! is_array($entry)) {
            return false;
        }

        if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
            $this->forget($user);

            return false;
        }

        if (! Hash::check($code, $entry['hash'])) {
            $entry['attempts']++;
            Cache::put($this->key($user), $entry, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        $this->forget($user);

        return true;
    }

    public function canResend(User $user): bool
    {
        return ! Cache::has($this->cooldownKey($user));
    }

    public function forget(User $user): void
    {
        Cache::forget($this->key($user));
        Cache::forget($this->cooldownKey($user));
    }

    private function key(User $user): string
    {
        return 'login.otp.'.$user->getKey();
    }

    private function cooldownKey(User $user): string
    {
        return 'login.otp.cooldown.'.$user->getKey();
    }
}
