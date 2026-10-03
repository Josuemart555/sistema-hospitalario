<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UsersDataTableTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_user_can_render_users_table(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertSee('id="users-table"', false)
            ->assertSee('Nuevo usuario');
    }

    public function test_unauthorized_user_cannot_request_users_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('admin.users.index', $this->dataTableQuery()))
            ->assertForbidden();
    }

    public function test_users_data_can_be_searched_and_escapes_user_content(): void
    {
        $administrator = $this->administrator();
        $role = Role::create(['name' => 'Especialistas', 'guard_name' => 'web']);
        $matchingUser = User::factory()->create([
            'name' => '<script>alert(1)</script>',
            'email' => 'coincide@example.test',
        ]);
        $matchingUser->assignRole($role);
        User::factory()->create(['email' => 'otro@example.test']);

        $response = $this->usersDataResponse($administrator, [
            'search' => ['value' => 'coincide', 'regex' => 'false'],
        ]);

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.roles', 'Especialistas');
        $this->assertStringContainsString('&lt;script&gt;', $response->json('data.0.name'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.name'));
    }

    public function test_users_data_can_be_ordered_and_paginated(): void
    {
        $administrator = $this->administrator();
        User::factory()->create(['name' => 'Alba']);
        User::factory()->create(['name' => 'Zulema']);

        $response = $this->usersDataResponse($administrator, [
            'length' => 1,
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]);

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 3);
        $this->assertStringContainsString('Zulema', $response->json('data.0.name'));
        $this->assertCount(1, $response->json('data'));
    }

    private function administrator(): User
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findByName('Super Administrador'));

        return $administrator;
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return TestResponse<JsonResponse>
     */
    private function usersDataResponse(User $administrator, array $parameters = []): TestResponse
    {
        return $this->actingAs($administrator)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.users.index', array_replace_recursive(
                $this->dataTableQuery(),
                $parameters,
            )));
    }

    /**
     * @return array<string, mixed>
     */
    private function dataTableQuery(): array
    {
        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => 'false'],
            'columns' => [
                ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'status', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'roles', 'name' => 'roles.name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ];
    }
}
