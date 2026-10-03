<?php

namespace App\Providers;

use App\Actions\EnableTwoFactorAuthentication;
use App\Http\Responses\GenericPasswordResetLinkResponse;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication as FortifyEnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FortifyEnableTwoFactorAuthentication::class, EnableTwoFactorAuthentication::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, GenericPasswordResetLinkResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
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
