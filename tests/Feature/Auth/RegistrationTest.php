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
        // Clear any stale tenant binding from a prior test in this process
        $this->app->forgetInstance('tenant');
    }

    public function test_registration_page_is_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_new_user_can_register(): void
    {
        $response = $this->post('/register', [
            'company_name'          => 'Acme Corp',
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_registered_user_has_admin_role(): void
    {
        $this->post('/register', [
            'company_name'          => 'Jane Corp',
            'name'                  => 'Jane Admin',
            'email'                 => 'jane@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_registration_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $this->post('/register', [
            'company_name'          => 'Event Corp',
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
            'company_name'          => 'Weak Corp',
            'name'                  => 'Weak',
            'email'                 => 'weak@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        // Register first to claim the email
        $this->post('/register', [
            'company_name'          => 'First Corp',
            'name'                  => 'First User',
            'email'                 => 'taken@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        // Log out so the guest middleware allows a second registration attempt
        auth()->logout();

        // Attempt duplicate registration — unique:users is a global check, not tenant-scoped
        $this->post('/register', [
            'company_name'          => 'Second Corp',
            'name'                  => 'Duplicate',
            'email'                 => 'taken@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->post('/register', [
            'company_name'          => 'No Confirm Corp',
            'name'                  => 'NoConfirm',
            'email'                 => 'noconfirm@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'DifferentPassword@123',
        ])->assertSessionHasErrors('password');
    }

    public function test_registration_requires_company_name(): void
    {
        $this->post('/register', [
            'name'                  => 'No Company',
            'email'                 => 'nocompany@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertSessionHasErrors('company_name');

        $this->assertGuest();
    }
}
