<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Roles must exist before any registration can assign one
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_page_is_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_new_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_registered_user_has_employee_role(): void
    {
        $this->post('/register', [
            'name'                  => 'Jane Employee',
            'email'                 => 'jane@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('employee'));
    }

    public function test_registration_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $this->post('/register', [
            'name'                  => 'Event User',
            'email'                 => 'event@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_requires_strong_password(): void
    {
        $this->post('/register', [
            'name'                  => 'Weak',
            'email'                 => 'weak@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name'                  => 'Duplicate',
            'email'                 => 'taken@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->post('/register', [
            'name'                  => 'NoConfirm',
            'email'                 => 'noconfirm@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'DifferentPassword@123',
        ])->assertSessionHasErrors('password');
    }
}
