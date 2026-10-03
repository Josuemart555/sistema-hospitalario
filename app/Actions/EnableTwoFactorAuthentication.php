<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication as FortifyAction;

class EnableTwoFactorAuthentication extends FortifyAction
{
    public function __invoke($user, $force = false): void
    {
        if (! $user->two_factor_allowed) {
            throw ValidationException::withMessages([
                'two_factor' => 'Un administrador debe habilitar esta opción antes de activarla.',
            ]);
        }

        parent::__invoke($user, $force);
    }
}
