<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdministrationAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_permission_cannot_open_user_administration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_administrator_can_open_user_administration(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('Super Administrador'));

        $this->actingAs($user)->get(route('admin.users.index'))->assertSee('Usuarios');
    }

    public function test_last_super_administrator_cannot_be_deleted(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::factory()->create();
        $administrator->givePermissionTo(Permission::findByName('usuarios.administrar'));
        $superAdministrator = User::factory()->create();
        $superAdministrator->assignRole(Role::findByName('Super Administrador'));

        $response = $this->actingAs($administrator)->delete(route('admin.users.destroy', $superAdministrator));

        $response->assertSessionHasErrors('user');
        $this->assertModelExists($superAdministrator);
    }
}
