<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->tenant = Tenant::factory()->create(['slug' => 'test', 'status' => 'active']);
        app()->instance('tenant', $this->tenant);
    }

    public function test_profile_page_is_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertStatus(200);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name'       => 'Updated Name',
            'email'      => 'updated@example.com',
            'phone'      => '+1-555-123-4567',
            'department' => 'Engineering',
            'position'   => 'Senior Developer',
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('+1-555-123-4567', $user->phone);
        $this->assertSame('Engineering', $user->department);
        $this->assertNull($user->email_verified_at);
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_old_avatar_is_deleted_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Upload first avatar
        $this->actingAs($user)->patch('/profile', [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $firstAvatar = $user->fresh()->avatar;

        // Upload second avatar — first should be deleted
        $this->actingAs($user)->patch('/profile', [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => UploadedFile::fake()->image('second.jpg'),
        ]);

        Storage::disk('public')->assertMissing($firstAvatar);
    }

    public function test_email_verification_reset_on_email_change(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->patch('/profile', [
            'name'  => $user->name,
            'email' => 'newemail@example.com',
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_wrong_password_prevents_account_deletion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profile', ['password' => 'wrong-password'])
             ->assertSessionHasErrorsIn('userDeletion');

        $this->assertAuthenticatedAs($user);
    }
}
