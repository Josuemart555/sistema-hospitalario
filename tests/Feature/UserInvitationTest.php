<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserInvitationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_creates_invited_user_and_sends_invitation(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::findByName('Super Administrador'));
        Notification::fake();

        $response = $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'María López', 'email' => 'maria@example.test', 'status' => 'invited', 'two_factor_allowed' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $invited = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame('invited', $invited->status);
        $this->assertTrue($invited->two_factor_allowed);
        Notification::assertSentTo($invited, UserInvitationNotification::class);
    }
}
