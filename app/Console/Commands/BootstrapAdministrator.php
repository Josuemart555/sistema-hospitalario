<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('app:bootstrap-administrator {email?}')]
#[Description('Crea o actualiza el primer superadministrador')]
class BootstrapAdministrator extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: $this->ask('Correo electrónico'));
        $name = (string) $this->ask('Nombre completo');
        $password = (string) $this->secret('Contraseña segura');
        if (mb_strlen($password) < 12) {
            $this->error('La contraseña debe tener al menos 12 caracteres.');

            return self::FAILURE;
        }
        $user = User::updateOrCreate(['email' => mb_strtolower($email)], [
            'name' => $name, 'password' => Hash::make($password), 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'Super Administrador', 'guard_name' => 'web']);
        $user->assignRole($role);
        $this->info('Superadministrador preparado correctamente.');

        return self::SUCCESS;
    }
}
