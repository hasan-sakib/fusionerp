<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_user_index(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_employee_cannot_access_user_list(): void
    {
        $this->actingAs($this->employee)
             ->get(route('users.index'))
             ->assertForbidden();
    }

    public function test_manager_can_view_user_list(): void
    {
        $this->actingAs($this->manager)
             ->get(route('users.index'))
             ->assertOk();
    }

    public function test_admin_can_view_user_list(): void
    {
        $this->actingAs($this->admin)
             ->get(route('users.index'))
             ->assertOk()
             ->assertSee($this->manager->name);
    }

    public function test_admin_can_search_users(): void
    {
        $target = User::factory()->create(['name' => 'Searchable Alice', 'department' => 'QA']);
        $target->assignRole('employee');

        $this->actingAs($this->admin)
             ->get(route('users.index', ['search' => 'Searchable Alice']))
             ->assertOk()
             ->assertSee('Searchable Alice');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $inactive = User::factory()->inactive()->create();
        $inactive->assignRole('employee');

        $response = $this->actingAs($this->admin)
                         ->get(route('users.index', ['status' => 'inactive']));

        $response->assertOk()->assertSee($inactive->name);
    }

    public function test_admin_can_filter_by_role(): void
    {
        $response = $this->actingAs($this->admin)
                         ->get(route('users.index', ['role' => 'manager']));

        $response->assertOk()->assertSee($this->manager->name);
    }

    public function test_admin_can_view_trashed_users(): void
    {
        $deleted = User::factory()->create(['name' => 'Deleted Dave']);
        $deleted->assignRole('employee');
        $deleted->delete();

        $response = $this->actingAs($this->admin)
                         ->get(route('users.index', ['trashed' => '1']));

        $response->assertOk()->assertSee('Deleted Dave');
    }

    public function test_manager_cannot_view_trashed_users(): void
    {
        $deleted = User::factory()->create(['name' => 'Deleted Dave']);
        $deleted->assignRole('employee');
        $deleted->delete();

        // Manager gets the page but trashed filter is ignored
        $response = $this->actingAs($this->manager)
                         ->get(route('users.index', ['trashed' => '1']));

        $response->assertOk()->assertDontSee('Deleted Dave');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_user_profile(): void
    {
        $this->actingAs($this->admin)
             ->get(route('users.show', $this->employee))
             ->assertOk()
             ->assertSee($this->employee->name);
    }

    public function test_manager_can_view_user_profile(): void
    {
        $this->actingAs($this->manager)
             ->get(route('users.show', $this->employee))
             ->assertOk();
    }

    public function test_employee_cannot_view_user_profile(): void
    {
        $this->actingAs($this->employee)
             ->get(route('users.show', $this->manager))
             ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_admin_can_access_create_user_form(): void
    {
        $this->actingAs($this->admin)
             ->get(route('users.create'))
             ->assertOk();
    }

    public function test_manager_cannot_access_create_user_form(): void
    {
        $this->actingAs($this->manager)
             ->get(route('users.create'))
             ->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'                  => 'New Employee',
            'email'                 => 'newemployee@example.com',
            'phone'                 => '555-1234',
            'department'            => 'Engineering',
            'position'              => 'Developer',
            'status'                => 'active',
            'role'                  => 'employee',
            'password'              => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $user = User::where('email', 'newemployee@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('employee'));
        $this->assertNotNull($user->email_verified_at);

        $response->assertRedirect(route('users.show', $user));
    }

    public function test_create_user_requires_name(): void
    {
        $this->actingAs($this->admin)
             ->post(route('users.store'), ['name' => ''])
             ->assertSessionHasErrors('name');
    }

    public function test_create_user_requires_unique_email(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name'                  => 'Duplicate',
            'email'                 => $this->employee->email,
            'status'                => 'active',
            'role'                  => 'employee',
            'password'              => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ])->assertSessionHasErrors('email');
    }

    public function test_create_user_requires_strong_password(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name'                  => 'Weak Pass User',
            'email'                 => 'weakpass@example.com',
            'status'                => 'active',
            'role'                  => 'employee',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');
    }

    public function test_create_user_requires_valid_role(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name'                  => 'No Role',
            'email'                 => 'norole@example.com',
            'status'                => 'active',
            'role'                  => 'superuser',
            'password'              => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ])->assertSessionHasErrors('role');
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_admin_can_access_edit_user_form(): void
    {
        $this->actingAs($this->admin)
             ->get(route('users.edit', $this->employee))
             ->assertOk();
    }

    public function test_manager_cannot_access_edit_user_form(): void
    {
        $this->actingAs($this->manager)
             ->get(route('users.edit', $this->employee))
             ->assertForbidden();
    }

    public function test_admin_can_update_a_user(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('users.update', $this->employee), [
            'name'       => 'Updated Name',
            'email'      => $this->employee->email,
            'phone'      => '999-0000',
            'department' => 'Updated Dept',
            'position'   => 'Updated Role',
            'status'     => 'active',
            'role'       => 'employee',
        ]);

        $this->employee->refresh();

        $this->assertEquals('Updated Name', $this->employee->name);
        $this->assertEquals('Updated Dept', $this->employee->department);
        $response->assertRedirect(route('users.show', $this->employee));
    }

    public function test_update_resets_email_verification_on_email_change(): void
    {
        $this->assertNotNull($this->employee->email_verified_at);

        $this->actingAs($this->admin)->patch(route('users.update', $this->employee), [
            'name'       => $this->employee->name,
            'email'      => 'new-email@example.com',
            'phone'      => $this->employee->phone,
            'department' => $this->employee->department,
            'position'   => $this->employee->position,
            'status'     => $this->employee->status,
            'role'       => 'employee',
        ]);

        $this->assertNull($this->employee->fresh()->email_verified_at);
    }

    public function test_update_requires_unique_email_excluding_self(): void
    {
        // Using own current email is valid
        $this->actingAs($this->admin)->patch(route('users.update', $this->employee), [
            'name'   => $this->employee->name,
            'email'  => $this->employee->email,
            'status' => 'active',
            'role'   => 'employee',
        ])->assertSessionHasNoErrors();

        // Using another user's email is invalid
        $this->actingAs($this->admin)->patch(route('users.update', $this->employee), [
            'name'   => $this->employee->name,
            'email'  => $this->manager->email,
            'status' => 'active',
            'role'   => 'employee',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_change_user_role(): void
    {
        $this->assertTrue($this->employee->hasRole('employee'));

        $this->actingAs($this->admin)->patch(route('users.update', $this->employee), [
            'name'       => $this->employee->name,
            'email'      => $this->employee->email,
            'phone'      => $this->employee->phone,
            'department' => $this->employee->department,
            'position'   => $this->employee->position,
            'status'     => $this->employee->status,
            'role'       => 'manager',
        ]);

        $this->employee->refresh();

        $this->assertTrue($this->employee->hasRole('manager'));
        $this->assertFalse($this->employee->hasRole('employee'));
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_user(): void
    {
        $target = User::factory()->create();
        $target->assignRole('employee');

        $this->actingAs($this->admin)
             ->delete(route('users.destroy', $target))
             ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_manager_cannot_delete_a_user(): void
    {
        $this->actingAs($this->manager)
             ->delete(route('users.destroy', $this->employee))
             ->assertForbidden();
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $this->actingAs($this->admin)
             ->delete(route('users.destroy', $this->admin))
             ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'deleted_at' => null]);
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function test_admin_can_restore_a_deleted_user(): void
    {
        $deleted = User::factory()->create();
        $deleted->assignRole('employee');
        $deleted->delete();

        $this->assertSoftDeleted('users', ['id' => $deleted->id]);

        $this->actingAs($this->admin)
             ->post(route('users.restore', $deleted->id))
             ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $deleted->id, 'deleted_at' => null]);
    }

    public function test_manager_cannot_restore_a_deleted_user(): void
    {
        $deleted = User::factory()->create();
        $deleted->assignRole('employee');
        $deleted->delete();

        $this->actingAs($this->manager)
             ->post(route('users.restore', $deleted->id))
             ->assertForbidden();
    }

    // ── Password Reset ────────────────────────────────────────────────────────

    public function test_admin_can_send_password_reset_email(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
             ->post(route('users.reset-password', $this->employee))
             ->assertRedirect();

        Notification::assertSentTo($this->employee, ResetPassword::class);
    }

    public function test_manager_cannot_send_password_reset_email(): void
    {
        $this->actingAs($this->manager)
             ->post(route('users.reset-password', $this->employee))
             ->assertForbidden();
    }
}
