<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function administrator(): User
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findByName('Super Administrador'));

        return $administrator;
    }

    public function test_administrator_creates_active_user_with_password_and_no_email(): void
    {
        $administrator = $this->administrator();
        Notification::fake();
        Mail::fake();

        $response = $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'María López', 'email' => 'maria@example.test', 'status' => 'active', 'two_factor_allowed' => '1',
            'password' => 'Clave-Segura-789!x', 'password_confirmation' => 'Clave-Segura-789!x',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->two_factor_allowed);
        $this->assertTrue(Hash::check('Clave-Segura-789!x', $user->password));
        Notification::assertNothingSent();
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_password_is_required_and_must_be_confirmed(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Sin Clave', 'email' => 'sin@example.test', 'status' => 'active',
            'password' => 'Clave-Segura-789!x', 'password_confirmation' => 'distinta',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'sin@example.test']);
    }

    public function test_roles_and_permissions_submitted_as_ids_are_assigned_on_create_and_update(): void
    {
        $administrator = $this->administrator();
        $role = Role::findByName('Super Administrador');
        $permission = \Spatie\Permission\Models\Permission::firstOrFail();

        $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Ana', 'email' => 'ana@example.test', 'status' => 'active',
            'password' => 'Clave-Segura-789!x', 'password_confirmation' => 'Clave-Segura-789!x',
            'roles' => [(string) $role->id], 'permissions' => [(string) $permission->id],
        ])->assertSessionHasNoErrors();
        $ana = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertTrue($ana->hasRole($role->name));
        $this->assertTrue($ana->hasDirectPermission($permission->name));

        $this->actingAs($administrator)->put(route('admin.users.update', $ana), [
            'name' => 'Ana', 'email' => 'ana@example.test', 'status' => 'active',
            'roles' => [(string) $role->id], 'permissions' => [(string) $permission->id],
        ])->assertRedirect(route('admin.users.index'));
        $this->assertTrue($ana->fresh()->hasRole($role->name));
    }
}
