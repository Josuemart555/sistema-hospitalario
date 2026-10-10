<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function userWithPassword(string $password = 'Clave-Actual-123!'): User
    {
        $user = User::factory()->create(['password' => Hash::make($password), 'status' => 'active']);
        $user->givePermissionTo(Permission::create(['name' => 'perfil.administrar', 'guard_name' => 'web']));

        return $user;
    }

    public function test_user_can_update_password_from_profile(): void
    {
        $user = $this->userWithPassword();

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('user-password.update'), [
            'current_password' => 'Clave-Actual-123!',
            'password' => 'Nueva-Clave-456!x',
            'password_confirmation' => 'Nueva-Clave-456!x',
        ]);

        $response->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Nueva-Clave-456!x', $user->fresh()->password));
        $this->assertStringStartsWith('$2y$', $user->fresh()->password);
        $this->assertSame(60, strlen($user->fresh()->password));
    }

    public function test_wrong_current_password_does_not_change_it(): void
    {
        $user = $this->userWithPassword();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('user-password.update'), [
            'current_password' => 'incorrecta',
            'password' => 'Nueva-Clave-456!x',
            'password_confirmation' => 'Nueva-Clave-456!x',
        ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

        $this->assertTrue(Hash::check('Clave-Actual-123!', $user->fresh()->password));
    }

    public function test_mismatched_confirmation_is_rejected(): void
    {
        $user = $this->userWithPassword();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('user-password.update'), [
            'current_password' => 'Clave-Actual-123!',
            'password' => 'Nueva-Clave-456!x',
            'password_confirmation' => 'otra',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->assertTrue(Hash::check('Clave-Actual-123!', $user->fresh()->password));
    }

    public function test_profile_page_shows_password_errors_and_success(): void
    {
        $user = $this->userWithPassword();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('user-password.update'), [
            'current_password' => 'incorrecta',
            'password' => 'Nueva-Clave-456!x',
            'password_confirmation' => 'Nueva-Clave-456!x',
        ]);
        $this->get(route('profile.edit'))->assertSee('Revise la información');

        $this->actingAs($user)->from(route('profile.edit'))->put(route('user-password.update'), [
            'current_password' => 'Clave-Actual-123!',
            'password' => 'Nueva-Clave-456!x',
            'password_confirmation' => 'Nueva-Clave-456!x',
        ]);
        $this->get(route('profile.edit'))->assertSee('Contraseña actualizada')->assertDontSee('password-updated');
    }
}
