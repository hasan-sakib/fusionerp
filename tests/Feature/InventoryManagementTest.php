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

class InventoryManagementTest extends TestCase
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

    public function test_guest_is_redirected_from_inventory_index(): void
    {
        $this->get(route('inventory.index'))->assertRedirect(route('login'));
    }

    public function test_employee_can_view_inventory_index(): void
    {
        $this->actingAs($this->employee)
             ->get(route('inventory.index'))
             ->assertOk();
    }

    public function test_inventory_index_shows_stats(): void
    {
        Product::factory()->count(3)->create(['stock_quantity' => 50, 'min_stock_level' => 5]);
        Product::factory()->create(['stock_quantity' => 0, 'min_stock_level' => 5]);
        Product::factory()->create(['stock_quantity' => 3, 'min_stock_level' => 5]);

        $response = $this->actingAs($this->admin)
                         ->get(route('inventory.index'))
                         ->assertOk();

        $response->assertSee('5'); // total products
        $response->assertSee('1'); // out of stock count
    }

    public function test_inventory_index_filters_low_stock(): void
    {
        Product::factory()->create(['name' => 'Normal Stock', 'stock_quantity' => 100, 'min_stock_level' => 5]);
        Product::factory()->create(['name' => 'Low Product', 'stock_quantity' => 3, 'min_stock_level' => 5]);

        $this->actingAs($this->admin)
             ->get(route('inventory.index', ['low_stock' => '1']))
             ->assertOk()
             ->assertSee('Low Product')
             ->assertDontSee('Normal Stock');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_employee_can_view_product_inventory(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->employee)
             ->get(route('inventory.show', $product))
             ->assertOk()
             ->assertSee($product->name);
    }

    public function test_inventory_show_displays_movement_history(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 20]);

        InventoryMovement::create([
            'product_id'      => $product->id,
            'user_id'         => $this->admin->id,
            'type'            => 'in',
            'quantity'        => 20,
            'before_quantity' => 0,
            'after_quantity'  => 20,
            'notes'           => 'Initial stock',
        ]);

        $this->actingAs($this->admin)
             ->get(route('inventory.show', $product))
             ->assertOk()
             ->assertSee('Initial stock');
    }

    // ── Adjust ────────────────────────────────────────────────────────────────

    public function test_employee_cannot_adjust_inventory(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($this->employee)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'add',
                 'quantity'        => 5,
             ])
             ->assertForbidden();
    }

    public function test_manager_can_add_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'add',
                 'quantity'        => 25,
                 'notes'           => 'Restocked from supplier',
             ])
             ->assertRedirect(route('inventory.show', $product));

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 35,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'in',
            'quantity'        => 25,
            'before_quantity' => 10,
            'after_quantity'  => 35,
            'notes'           => 'Restocked from supplier',
        ]);
    }

    public function test_manager_can_subtract_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'subtract',
                 'quantity'        => 15,
                 'notes'           => 'Damaged goods removed',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 35,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'out',
            'before_quantity' => 50,
            'after_quantity'  => 35,
        ]);
    }

    public function test_subtract_cannot_go_below_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'subtract',
                 'quantity'        => 100,
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 0,
        ]);
    }

    public function test_manager_can_set_stock_to_specific_value(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 30]);

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'set',
                 'quantity'        => 100,
                 'notes'           => 'Physical count correction',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 100,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'adjustment',
            'before_quantity' => 30,
            'after_quantity'  => 100,
        ]);
    }

    public function test_adjustment_validates_required_fields(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [])
             ->assertSessionHasErrors(['adjustment_type', 'quantity']);
    }

    public function test_adjustment_type_must_be_valid(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'invalid_type',
                 'quantity'        => 10,
             ])
             ->assertSessionHasErrors('adjustment_type');
    }

    public function test_quantity_must_be_non_negative(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'add',
                 'quantity'        => -5,
             ])
             ->assertSessionHasErrors('quantity');
    }

    public function test_adjustment_records_correct_user(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($this->manager)
             ->post(route('inventory.adjust', $product), [
                 'adjustment_type' => 'add',
                 'quantity'        => 10,
             ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'user_id'    => $this->manager->id,
        ]);
    }

    // ── Movements ─────────────────────────────────────────────────────────────

    public function test_guest_redirected_from_movements_log(): void
    {
        $this->get(route('inventory.movements'))->assertRedirect(route('login'));
    }

    public function test_employee_can_view_movements_log(): void
    {
        $this->actingAs($this->employee)
             ->get(route('inventory.movements'))
             ->assertOk();
    }

    public function test_movements_can_be_filtered_by_product(): void
    {
        $productA = Product::factory()->create(['name' => 'Product A', 'stock_quantity' => 10]);
        $productB = Product::factory()->create(['name' => 'Product B', 'stock_quantity' => 20]);

        InventoryMovement::create([
            'product_id' => $productA->id, 'user_id' => $this->admin->id,
            'type' => 'in', 'quantity' => 10, 'before_quantity' => 0, 'after_quantity' => 10,
            'notes' => 'Notes for product A only',
        ]);
        InventoryMovement::create([
            'product_id' => $productB->id, 'user_id' => $this->admin->id,
            'type' => 'in', 'quantity' => 20, 'before_quantity' => 0, 'after_quantity' => 20,
            'notes' => 'Notes for product B only',
        ]);

        // Both product names appear in the filter dropdown; assert on movement notes instead
        $this->actingAs($this->admin)
             ->get(route('inventory.movements', ['product_id' => $productA->id]))
             ->assertOk()
             ->assertSee('Notes for product A only')
             ->assertDontSee('Notes for product B only');
    }

    public function test_movements_can_be_filtered_by_type(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        InventoryMovement::create([
            'product_id' => $product->id, 'user_id' => $this->admin->id,
            'type' => 'in', 'quantity' => 50, 'before_quantity' => 0, 'after_quantity' => 50,
            'notes' => 'Notes for in movement',
        ]);
        InventoryMovement::create([
            'product_id' => $product->id, 'user_id' => $this->admin->id,
            'type' => 'out', 'quantity' => 10, 'before_quantity' => 50, 'after_quantity' => 40,
            'notes' => 'Notes for out movement',
        ]);

        $this->actingAs($this->admin)
             ->get(route('inventory.movements', ['type' => 'out']))
             ->assertOk()
             ->assertSee('Notes for out movement')
             ->assertDontSee('Notes for in movement');
    }

    // ── Category Management ───────────────────────────────────────────────────

    public function test_guest_is_redirected_from_categories(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }

    public function test_employee_can_view_categories(): void
    {
        $this->actingAs($this->employee)
             ->get(route('categories.index'))
             ->assertOk();
    }

    public function test_employee_cannot_create_category(): void
    {
        $this->actingAs($this->employee)
             ->get(route('categories.create'))
             ->assertForbidden();
    }

    public function test_manager_can_create_category(): void
    {
        $this->actingAs($this->manager)
             ->post(route('categories.store'), [
                 'name'      => 'Electronics',
                 'is_active' => '1',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('categories', ['name' => 'Electronics', 'slug' => 'electronics']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Electronics']);

        $this->actingAs($this->admin)
             ->post(route('categories.store'), [
                 'name'      => 'Electronics',
                 'is_active' => '1',
             ])
             ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
             ->patch(route('categories.update', $category), [
                 'name'      => 'New Name',
                 'is_active' => '1',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
             ->delete(route('categories.destroy', $category))
             ->assertRedirect()
             ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
             ->delete(route('categories.destroy', $category))
             ->assertRedirect(route('categories.index'));

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
