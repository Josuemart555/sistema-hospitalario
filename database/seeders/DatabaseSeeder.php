<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(config('administration.protected_permissions'))->mapWithKeys(function (string $name): array {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            return [$name => $permission];
        });
        $role = Role::firstOrCreate(['name' => 'Super Administrador', 'guard_name' => 'web']);

        $dashboard = Option::updateOrCreate(['route_name' => 'dashboard'], ['name' => 'Inicio', 'icon' => 'bi-house', 'sort_order' => 10, 'permission_id' => $permissions['dashboard.ver']->id, 'is_active' => true]);
        $administration = Option::updateOrCreate(['name' => 'Administración', 'route_name' => null], ['icon' => 'bi-gear', 'sort_order' => 20, 'is_active' => true]);
        $items = [
            ['Usuarios', 'admin.users.index', 'bi-people', 'usuarios.administrar', 10],
            ['Roles', 'admin.roles.index', 'bi-person-badge', 'roles.administrar', 20],
            ['Permisos', 'admin.permissions.index', 'bi-shield-check', 'permisos.administrar', 30],
            ['Opciones del menú', 'admin.options.index', 'bi-list-nested', 'opciones.administrar', 40],
        ];
        $options = collect([$dashboard, $administration]);
        foreach ($items as [$name, $route, $icon, $permission, $order]) {
            $options->push(Option::updateOrCreate(['route_name' => $route], ['parent_id' => $administration->id, 'name' => $name, 'icon' => $icon, 'sort_order' => $order, 'permission_id' => $permissions[$permission]->id, 'is_active' => true]));
        }
        $role->options()->syncWithoutDetaching($options->pluck('id'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
