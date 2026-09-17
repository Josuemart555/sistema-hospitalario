<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdministrationDataTablesTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('administrationTables')]
    public function test_authorized_user_can_render_administration_table(string $routeName, string $tableId): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route($routeName))
            ->assertSee('id="'.$tableId.'"', false);
    }

    #[DataProvider('administrationTables')]
    public function test_unauthorized_user_cannot_request_administration_data(string $routeName, string $tableId): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route($routeName, ['draw' => 1]))
            ->assertForbidden();
    }

    public function test_roles_data_can_be_searched_and_includes_relationship_counts(): void
    {
        $administrator = $this->administrator();
        $role = Role::create(['name' => 'Clínicos <script>alert(1)</script>', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'clinica.ver', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        User::factory()->create()->assignRole($role);

        $response = $this->dataResponse($administrator, 'admin.roles.index', $this->rolesQuery('Clínicos'));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.users_count', 1)
            ->assertJsonPath('data.0.permissions_count', 1);
        $this->assertStringContainsString('&lt;script&gt;', $response->json('data.0.name'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.name'));
    }

    public function test_permissions_data_can_be_searched_and_includes_role_count(): void
    {
        $administrator = $this->administrator();
        $permission = Permission::create(['name' => 'riesgo.<script>alert(1)</script>', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Auditor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $response = $this->dataResponse($administrator, 'admin.permissions.index', $this->permissionsQuery('riesgo'));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.roles_count', 1);
        $this->assertStringContainsString('&lt;script&gt;', $response->json('data.0.name'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.name'));
    }

    public function test_options_data_can_be_searched_and_includes_relationships(): void
    {
        $administrator = $this->administrator();
        $permission = Permission::create(['name' => 'agenda.ver', 'guard_name' => 'web']);
        $parent = Option::factory()->create(['name' => 'Agenda', 'route_name' => null]);
        Option::factory()->create([
            'parent_id' => $parent->id,
            'permission_id' => $permission->id,
            'name' => 'Consulta <script>alert(1)</script>',
            'route_name' => 'agenda.index',
            'is_active' => false,
        ]);

        $response = $this->dataResponse($administrator, 'admin.options.index', $this->optionsQuery('Consulta'));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.permission', 'agenda.ver');
        $this->assertStringContainsString('&lt;script&gt;', $response->json('data.0.name'));
        $this->assertStringContainsString('Inactiva', $response->json('data.0.is_active'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.name'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function administrationTables(): array
    {
        return [
            'roles' => ['admin.roles.index', 'roles-table'],
            'permissions' => ['admin.permissions.index', 'permissions-table'],
            'options' => ['admin.options.index', 'options-table'],
        ];
    }

    private function administrator(): User
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findByName('Super Administrador'));

        return $administrator;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return TestResponse<JsonResponse>
     */
    private function dataResponse(User $administrator, string $routeName, array $query): TestResponse
    {
        return $this->actingAs($administrator)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route($routeName, $query));
    }

    /**
     * @return array<string, mixed>
     */
    private function rolesQuery(string $search): array
    {
        return $this->dataTableQuery($search, [
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'users_count', 'name' => 'users_count', 'searchable' => 'false', 'orderable' => 'true'],
            ['data' => 'permissions_count', 'name' => 'permissions_count', 'searchable' => 'false', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function permissionsQuery(string $search): array
    {
        return $this->dataTableQuery($search, [
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'roles_count', 'name' => 'roles_count', 'searchable' => 'false', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function optionsQuery(string $search): array
    {
        return $this->dataTableQuery($search, [
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'route_name', 'name' => 'route_name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'permission', 'name' => 'permission.name', 'searchable' => 'true', 'orderable' => 'false'],
            ['data' => 'is_active', 'name' => 'is_active', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => 'sort_order', 'name' => 'sort_order', 'searchable' => 'false', 'orderable' => 'true'],
        ]);
    }

    /**
     * @param  array<int, array<string, string>>  $columns
     * @return array<string, mixed>
     */
    private function dataTableQuery(string $search, array $columns): array
    {
        $columns = array_map(function (array $column): array {
            $column['search'] = ['value' => '', 'regex' => 'false'];

            return $column;
        }, $columns);

        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => $search, 'regex' => 'false'],
            'columns' => $columns,
            'order' => [['column' => 0, 'dir' => 'asc']],
        ];
    }
}
