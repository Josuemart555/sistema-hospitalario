<?php

use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->middleware('can:dashboard.ver')->name('dashboard');
    Route::get('/perfil', [ProfileController::class, 'edit'])->middleware('can:perfil.administrar')->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->middleware('can:perfil.administrar')->name('profile.update');
    Route::delete('/perfil/avatar', [ProfileController::class, 'destroyAvatar'])->middleware('can:perfil.administrar')->name('profile.avatar.destroy');

    Route::prefix('administracion')->name('admin.')->group(function (): void {
        Route::post('usuarios/{user}/reenviar-invitacion', [UserController::class, 'resendInvitation'])->middleware('can:usuarios.administrar')->name('users.resend-invitation');
        Route::resource('usuarios', UserController::class)->parameters(['usuarios' => 'user'])->middleware('can:usuarios.administrar')->names('users');
        Route::resource('roles', RoleController::class)->middleware('can:roles.administrar')->names('roles');
        Route::resource('permisos', PermissionController::class)->parameters(['permisos' => 'permission'])->middleware('can:permisos.administrar')->names('permissions');
        Route::resource('opciones', OptionController::class)->parameters(['opciones' => 'option'])->middleware('can:opciones.administrar')->names('options');
    });
});
