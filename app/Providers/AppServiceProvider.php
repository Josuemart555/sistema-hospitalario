<?php

namespace App\Providers;

use App\Http\Responses\GenericPasswordResetLinkResponse;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, GenericPasswordResetLinkResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        ResetPassword::toMailUsing(fn (object $notifiable, string $token): MailMessage => (new MailMessage)
            ->subject('Restablecer contraseña')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de su cuenta.')
            ->action('Restablecer contraseña', url(route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
            ->line('Este enlace vence en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
            ->line('Si usted no lo solicitó, no es necesario hacer nada.')
            ->salutation('Saludos, '.config('app.name')));
        Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised());
        Event::listen(Login::class, fn (Login $event) => Log::info('Inicio de sesión correcto.', ['user_id' => $event->user->getAuthIdentifier(), 'ip' => request()->ip()]));
        Event::listen(Logout::class, fn (Logout $event) => Log::info('Cierre de sesión.', ['user_id' => $event->user?->getAuthIdentifier(), 'ip' => request()->ip()]));
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('Super Administrador') ? true : null;
        });

        View::composer('layouts.app', function ($view): void {
            $view->with('menuOptions', auth()->check() ? app(MenuService::class)->for(auth()->user()) : collect());
        });
    }
}
