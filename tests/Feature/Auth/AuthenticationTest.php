<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_last_login_at_is_recorded_on_login(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->assertNull($user->last_login_at);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->suspended()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $user = User::factory()->create();

        // Exhaust the 5 attempts
        foreach (range(0, 4) as $_) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
             ->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')
             ->assertRedirect(route('login'));
    }

    public function test_remember_me_sets_cookie(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertCookie(auth()->guard()->getRecallerName());
    }
}
