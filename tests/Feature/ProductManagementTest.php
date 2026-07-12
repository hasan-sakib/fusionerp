<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_guest_is_redirected_from_products_index(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_employee_can_view_products_list(): void
    {
        $this->actingAs($this->employee)
             ->get(route('products.index'))
             ->assertOk();
    }

    public function test_admin_can_view_products_list(): void
    {
        Product::factory()->count(3)->create();

        $this->actingAs($this->admin)
             ->get(route('products.index'))
             ->assertOk()
             ->assertSee(Product::first()->name);
    }

    public function test_products_index_supports_search(): void
    {
        Product::factory()->create(['name' => 'Unique Widget Alpha']);
        Product::factory()->create(['name' => 'Different Item']);

        $this->actingAs($this->admin)
             ->get(route('products.index', ['search' => 'Widget']))
             ->assertOk()
             ->assertSee('Unique Widget Alpha')
             ->assertDontSee('Different Item');
    }

    public function test_products_index_filters_by_status(): void
    {
        Product::factory()->create(['name' => 'Active Product', 'status' => 'active']);
        Product::factory()->create(['name' => 'Draft Product', 'status' => 'draft']);

        $this->actingAs($this->admin)
             ->get(route('products.index', ['status' => 'active']))
             ->assertOk()
             ->assertSee('Active Product')
             ->assertDontSee('Draft Product');
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_employee_cannot_access_create_form(): void
    {
        $this->actingAs($this->employee)
             ->get(route('products.create'))
             ->assertForbidden();
    }

    public function test_manager_can_access_create_form(): void
    {
        $this->actingAs($this->manager)
             ->get(route('products.create'))
             ->assertOk();
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
             ->post(route('products.store'), [
                 'name'            => 'Test Gadget Pro',
                 'category_id'     => $category->id,
                 'sku'             => 'TGP-001',
                 'price'           => '149.99',
                 'cost'            => '75.00',
                 'stock_quantity'  => 50,
                 'min_stock_level' => 10,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name'  => 'Test Gadget Pro',
            'sku'   => 'TGP-001',
            'price' => '149.99',
        ]);
    }

    public function test_creating_product_with_stock_records_inventory_movement(): void
    {
        $this->actingAs($this->admin)
             ->post(route('products.store'), [
                 'name'            => 'Stocked Item',
                 'price'           => '29.99',
                 'stock_quantity'  => 100,
                 'min_stock_level' => 5,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertRedirect();

        $product = Product::where('name', 'Stocked Item')->firstOrFail();

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'in',
            'quantity'        => 100,
            'before_quantity' => 0,
            'after_quantity'  => 100,
        ]);
    }

    public function test_creating_product_with_zero_stock_records_no_movement(): void
    {
        $this->actingAs($this->admin)
             ->post(route('products.store'), [
                 'name'            => 'Empty Item',
                 'price'           => '9.99',
                 'stock_quantity'  => 0,
                 'min_stock_level' => 0,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertRedirect();

        $product = Product::where('name', 'Empty Item')->firstOrFail();

        $this->assertDatabaseMissing('inventory_movements', [
            'product_id' => $product->id,
        ]);
    }

    public function test_product_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
             ->post(route('products.store'), [])
             ->assertSessionHasErrors(['name', 'price', 'stock_quantity', 'min_stock_level', 'status']);
    }

    public function test_product_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'DUPE-001']);

        $this->actingAs($this->admin)
             ->post(route('products.store'), [
                 'name'            => 'Another Product',
                 'sku'             => 'DUPE-001',
                 'price'           => '10.00',
                 'stock_quantity'  => 0,
                 'min_stock_level' => 0,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertSessionHasErrors('sku');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_employee_can_view_product_detail(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->employee)
             ->get(route('products.show', $product))
             ->assertOk()
             ->assertSee($product->name);
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_employee_cannot_edit_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->employee)
             ->get(route('products.edit', $product))
             ->assertForbidden();
    }

    public function test_manager_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name', 'price' => '10.00']);

        $this->actingAs($this->manager)
             ->patch(route('products.update', $product), [
                 'name'            => 'Updated Name',
                 'price'           => '19.99',
                 'min_stock_level' => 5,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'name'  => 'Updated Name',
            'price' => '19.99',
        ]);
    }

    public function test_update_does_not_change_stock_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 42, 'price' => '10.00']);

        $this->actingAs($this->manager)
             ->patch(route('products.update', $product), [
                 'name'            => $product->name,
                 'price'           => '15.00',
                 'min_stock_level' => 5,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 42,
        ]);
    }

    public function test_sku_can_be_reused_on_self_update(): void
    {
        $product = Product::factory()->create(['sku' => 'MY-SKU', 'price' => '10.00']);

        $this->actingAs($this->admin)
             ->patch(route('products.update', $product), [
                 'name'            => $product->name,
                 'sku'             => 'MY-SKU',
                 'price'           => '10.00',
                 'min_stock_level' => 0,
                 'status'          => 'active',
                 'is_featured'     => '0',
             ])
             ->assertSessionDoesntHaveErrors('sku');
    }

    // ── Delete / Restore ──────────────────────────────────────────────────────

    public function test_employee_cannot_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->employee)
             ->delete(route('products.destroy', $product))
             ->assertForbidden();
    }

    public function test_manager_cannot_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
             ->delete(route('products.destroy', $product))
             ->assertForbidden();
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
             ->delete(route('products.destroy', $product))
             ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_restore_soft_deleted_product(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->actingAs($this->admin)
             ->post(route('products.restore', $product->id))
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'         => $product->id,
            'deleted_at' => null,
        ]);
    }
}
