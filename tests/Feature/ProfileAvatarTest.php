<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_upload_a_valid_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'perfil.administrar', 'guard_name' => 'web']));

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name, 'email' => $user->email, 'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $media = $user->fresh()->getFirstMedia('avatar');
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_profile_rejects_non_image_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'perfil.administrar', 'guard_name' => 'web']));

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => $user->name, 'email' => $user->email, 'avatar' => UploadedFile::fake()->create('document.pdf', 30, 'application/pdf'),
        ]);

        $response->assertRedirect(route('profile.edit'))->assertSessionHasErrors('avatar');
        $this->assertFalse($user->fresh()->hasMedia('avatar'));
    }
}
