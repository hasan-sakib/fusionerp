<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->tenant = Tenant::factory()->create(['slug' => 'test', 'status' => 'active']);
        app()->instance('tenant', $this->tenant);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_roles_index(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
    }

    public function test_employee_cannot_access_roles_list(): void
    {
        $this->actingAs($this->employee)
             ->get(route('roles.index'))
             ->assertForbidden();
    }

    public function test_manager_cannot_access_roles_list(): void
    {
        $this->actingAs($this->manager)
             ->get(route('roles.index'))
             ->assertForbidden();
    }

    public function test_admin_can_view_roles_list(): void
    {
        $this->actingAs($this->admin)
             ->get(route('roles.index'))
             ->assertOk()
             ->assertSee('admin')
             ->assertSee('manager')
             ->assertSee('employee');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_role_detail(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $this->actingAs($this->admin)
             ->get(route('roles.show', $adminRole))
             ->assertOk()
             ->assertSee('admin');
    }

    public function test_non_admin_cannot_view_role_detail(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $this->actingAs($this->manager)
             ->get(route('roles.show', $adminRole))
             ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_admin_can_access_create_role_form(): void
    {
        $this->actingAs($this->admin)
             ->get(route('roles.create'))
             ->assertOk();
    }

    public function test_non_admin_cannot_access_create_role_form(): void
    {
        $this->actingAs($this->manager)
             ->get(route('roles.create'))
             ->assertForbidden();
    }

    public function test_admin_can_create_a_role_with_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name'        => 'support',
            'permissions' => ['users.view', 'products.view'],
        ]);

        $role = Role::where('name', 'support')->first();

        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));

        $response->assertRedirect(route('roles.show', $role));
    }

    public function test_create_role_requires_name(): void
    {
        $this->actingAs($this->admin)
             ->post(route('roles.store'), ['name' => ''])
             ->assertSessionHasErrors('name');
    }

    public function test_create_role_name_must_be_unique(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'admin',
        ])->assertSessionHasErrors('name');
    }

    public function test_create_role_name_must_be_lowercase(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'SuperAdmin',
        ])->assertSessionHasErrors('name');
    }

    public function test_create_role_with_invalid_permission_fails(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), [
            'name'        => 'testrole',
            'permissions' => ['nonexistent.permission'],
        ])->assertSessionHasErrors('permissions.0');
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_admin_can_access_edit_role_form(): void
    {
        $role = Role::where('name', 'employee')->first();

        $this->actingAs($this->admin)
             ->get(route('roles.edit', $role))
             ->assertOk();
    }

    public function test_non_admin_cannot_access_edit_role_form(): void
    {
        $role = Role::where('name', 'employee')->first();

        $this->actingAs($this->manager)
             ->get(route('roles.edit', $role))
             ->assertForbidden();
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::create(['name' => 'tester', 'guard_name' => 'web']);

        $this->actingAs($this->admin)->patch(route('roles.update', $role), [
            'name'        => 'tester',
            'permissions' => ['products.view', 'orders.view'],
        ]);

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertTrue($role->hasPermissionTo('orders.view'));
        $this->assertFalse($role->hasPermissionTo('users.view'));
    }

    public function test_admin_can_rename_a_custom_role(): void
    {
        $role = Role::create(['name' => 'oldrole', 'guard_name' => 'web']);

        $this->actingAs($this->admin)->patch(route('roles.update', $role), [
            'name'        => 'newrole',
            'permissions' => [],
        ]);

        $this->assertEquals('newrole', $role->fresh()->name);
        $this->assertDatabaseMissing('roles', ['name' => 'oldrole']);
    }

    public function test_admin_cannot_rename_built_in_roles(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $this->actingAs($this->admin)->patch(route('roles.update', $adminRole), [
            'name'        => 'superadmin',
            'permissions' => [],
        ]);

        $this->assertEquals('admin', $adminRole->fresh()->name);
    }

    public function test_clearing_permissions_removes_all(): void
    {
        $role = Role::create(['name' => 'clearme', 'guard_name' => 'web']);
        $role->syncPermissions(['products.view']);

        $this->assertTrue($role->hasPermissionTo('products.view'));

        $this->actingAs($this->admin)->patch(route('roles.update', $role), [
            'name'        => 'clearme',
            'permissions' => [],
        ]);

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('products.view'));
        $this->assertCount(0, $role->permissions);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_custom_role(): void
    {
        $role = Role::create(['name' => 'disposable', 'guard_name' => 'web']);

        $this->actingAs($this->admin)
             ->delete(route('roles.destroy', $role))
             ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'disposable']);
    }

    public function test_admin_cannot_delete_built_in_roles(): void
    {
        $managerRole = Role::where('name', 'manager')->first();

        $this->actingAs($this->admin)
             ->delete(route('roles.destroy', $managerRole))
             ->assertRedirect();

        $this->assertDatabaseHas('roles', ['name' => 'manager']);
    }

    public function test_admin_cannot_delete_role_with_assigned_users(): void
    {
        $role = Role::create(['name' => 'occupied', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('occupied');

        $this->actingAs($this->admin)
             ->delete(route('roles.destroy', $role))
             ->assertRedirect();

        $this->assertDatabaseHas('roles', ['name' => 'occupied']);
    }

    public function test_non_admin_cannot_delete_a_role(): void
    {
        $role = Role::where('name', 'employee')->first();

        $this->actingAs($this->manager)
             ->delete(route('roles.destroy', $role))
             ->assertForbidden();
    }
}
