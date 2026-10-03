<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MenuServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_menu_combines_role_and_direct_user_options_and_checks_permissions(): void
    {
        Cache::clear();
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Recepción', 'guard_name' => 'web']);
        $user->assignRole($role);
        $permission = Permission::create(['name' => 'citas.ver', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $roleOption = Option::create(['name' => 'Inicio', 'route_name' => 'dashboard', 'icon' => 'bi-house', 'is_active' => true]);
        $directOption = Option::create(['name' => 'Citas', 'route_name' => 'profile.edit', 'icon' => 'bi-calendar', 'permission_id' => $permission->id, 'is_active' => true]);
        $hiddenOption = Option::create(['name' => 'Oculta', 'route_name' => 'admin.users.index', 'icon' => 'bi-eye-slash', 'is_active' => false]);
        $role->options()->attach([$roleOption->id, $hiddenOption->id]);
        $user->options()->attach($directOption);

        $menu = app(MenuService::class)->for($user);

        $this->assertSame(['Citas', 'Inicio'], $menu->pluck('name')->sort()->values()->all());
    }
}
