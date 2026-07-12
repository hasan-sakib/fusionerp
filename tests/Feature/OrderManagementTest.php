<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;
    private Tenant $tenant;
    private OrderService $orderService;

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

        $this->orderService = $this->app->make(OrderService::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function productWithStock(int $stock = 100, float $price = 10.00): Product
    {
        return Product::factory()->create([
            'stock_quantity' => $stock,
            'price'          => $price,
            'status'         => 'active',
        ]);
    }

    private function makeOrder(User $user, int $qty = 5): Order
    {
        $product = $this->productWithStock(100);

        return $this->orderService->createOrder([
            'customer_name'   => 'Test Customer',
            'customer_email'  => 'test@example.com',
            'discount_amount' => 0,
            'tax_rate'        => 0,
            'items'           => [['product_id' => $product->id, 'quantity' => $qty]],
        ], $user);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_orders_index(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_employee_can_view_orders_list(): void
    {
        $this->actingAs($this->employee)
             ->get(route('orders.index'))
             ->assertOk();
    }

    public function test_orders_index_shows_existing_orders(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->admin)
             ->get(route('orders.index'))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    public function test_orders_index_can_filter_by_status(): void
    {
        $pending   = $this->makeOrder($this->admin);
        $confirmed = $this->makeOrder($this->admin);
        $confirmed->update(['status' => 'confirmed']);

        $this->actingAs($this->admin)
             ->get(route('orders.index', ['status' => 'pending']))
             ->assertOk()
             ->assertSee($pending->order_number)
             ->assertDontSee($confirmed->order_number);
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function test_guest_cannot_access_create_form(): void
    {
        $this->get(route('orders.create'))->assertRedirect(route('login'));
    }

    public function test_employee_can_access_create_form(): void
    {
        $this->actingAs($this->employee)
             ->get(route('orders.create'))
             ->assertOk();
    }

    public function test_employee_can_place_an_order(): void
    {
        $product = $this->productWithStock(50, 25.00);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Jane Doe',
                 'customer_email'  => 'jane@example.com',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [
                     ['product_id' => $product->id, 'quantity' => 3],
                 ],
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Jane Doe',
            'status'        => 'pending',
            'subtotal'      => '75.00',
            'total_amount'  => '75.00',
        ]);
    }

    public function test_placing_order_deducts_inventory(): void
    {
        $product = $this->productWithStock(20);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Test Customer',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [['product_id' => $product->id, 'quantity' => 7]],
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'stock_quantity' => 13,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'out',
            'quantity'        => 7,
            'before_quantity' => 20,
            'after_quantity'  => 13,
        ]);
    }

    public function test_placing_order_with_multiple_items_deducts_each_product(): void
    {
        $productA = $this->productWithStock(50, 10.00);
        $productB = $this->productWithStock(30, 20.00);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Multi Item Customer',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [
                     ['product_id' => $productA->id, 'quantity' => 5],
                     ['product_id' => $productB->id, 'quantity' => 3],
                 ],
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock_quantity' => 45]);
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'stock_quantity' => 27]);

        // subtotal = (5 × 10) + (3 × 20) = 50 + 60 = 110
        $this->assertDatabaseHas('orders', ['subtotal' => '110.00', 'total_amount' => '110.00']);
    }

    public function test_order_calculates_tax_and_discount_correctly(): void
    {
        $product = $this->productWithStock(100, 100.00);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Tax Customer',
                 'discount_amount' => '10',
                 'tax_rate'        => '10',
                 'items'           => [['product_id' => $product->id, 'quantity' => 2]],
             ])
             ->assertRedirect();

        // subtotal=200, tax=20, discount=10, total=210
        $this->assertDatabaseHas('orders', [
            'subtotal'        => '200.00',
            'tax_amount'      => '20.00',
            'discount_amount' => '10.00',
            'total_amount'    => '210.00',
        ]);
    }

    public function test_order_snapshots_product_name_and_price(): void
    {
        $product = $this->productWithStock(50, 99.99);
        $product->update(['name' => 'Original Name']);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Snapshot Customer',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [['product_id' => $product->id, 'quantity' => 1]],
             ]);

        // Change name and price after order
        $product->update(['name' => 'Changed Name', 'price' => 199.99]);

        $this->assertDatabaseHas('order_items', [
            'product_id'   => $product->id,
            'product_name' => 'Original Name',
            'unit_price'   => '99.99',
        ]);
    }

    public function test_insufficient_stock_prevents_order_creation(): void
    {
        $product = $this->productWithStock(5);

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Test',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [['product_id' => $product->id, 'quantity' => 10]],
             ])
             ->assertRedirect()
             ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['customer_name' => 'Test']);
        // Stock must be unchanged
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 5]);
    }

    public function test_insufficient_stock_rolls_back_entire_transaction(): void
    {
        $productA = $this->productWithStock(50, 10.00);
        $productB = $this->productWithStock(2, 10.00);  // too little stock

        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Rollback Customer',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [
                     ['product_id' => $productA->id, 'quantity' => 5],
                     ['product_id' => $productB->id, 'quantity' => 10],
                 ],
             ])
             ->assertSessionHas('error');

        // Product A stock must be untouched (transaction rolled back)
        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock_quantity' => 50]);
    }

    public function test_order_creation_validates_required_fields(): void
    {
        $this->actingAs($this->employee)
             ->post(route('orders.store'), [])
             ->assertSessionHasErrors(['customer_name', 'items']);
    }

    public function test_order_creation_requires_valid_product(): void
    {
        $this->actingAs($this->employee)
             ->post(route('orders.store'), [
                 'customer_name'   => 'Test',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [['product_id' => 99999, 'quantity' => 1]],
             ])
             ->assertSessionHasErrors('items.0.product_id');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_employee_can_view_order_detail(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->employee)
             ->get(route('orders.show', $order))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_employee_cannot_edit_order(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->employee)
             ->get(route('orders.edit', $order))
             ->assertForbidden();
    }

    public function test_manager_can_edit_pending_order(): void
    {
        $order = $this->makeOrder($this->manager);

        $this->actingAs($this->manager)
             ->get(route('orders.edit', $order))
             ->assertOk();
    }

    public function test_cannot_edit_confirmed_order(): void
    {
        $order = $this->makeOrder($this->manager);
        $order->update(['status' => 'confirmed']);

        $this->actingAs($this->manager)
             ->get(route('orders.edit', $order))
             ->assertForbidden();
    }

    public function test_editing_order_reconciles_inventory(): void
    {
        $productA = $this->productWithStock(100, 10.00);
        $productB = $this->productWithStock(50, 20.00);

        $order = $this->orderService->createOrder([
            'customer_name'   => 'Edit Customer',
            'discount_amount' => 0,
            'tax_rate'        => 0,
            'items'           => [['product_id' => $productA->id, 'quantity' => 10]],
        ], $this->manager);

        // After create: productA has 90 in stock
        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock_quantity' => 90]);

        // Edit: swap product A (10 units) for product B (5 units)
        $this->actingAs($this->manager)
             ->patch(route('orders.update', $order), [
                 'customer_name'   => 'Edit Customer',
                 'discount_amount' => '0',
                 'tax_rate'        => '0',
                 'items'           => [['product_id' => $productB->id, 'quantity' => 5]],
             ])
             ->assertRedirect(route('orders.show', $order));

        // Product A should have its 10 units restored (back to 100)
        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock_quantity' => 100]);
        // Product B should have 5 deducted (50 - 5 = 45)
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'stock_quantity' => 45]);
    }

    // ── Status Transitions ────────────────────────────────────────────────────

    public function test_employee_cannot_change_order_status(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->employee)
             ->patch(route('orders.status', $order), ['status' => 'confirmed'])
             ->assertForbidden();
    }

    public function test_manager_can_confirm_pending_order(): void
    {
        $order = $this->makeOrder($this->manager);

        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'confirmed'])
             ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    public function test_manager_can_progress_order_through_full_lifecycle(): void
    {
        $order = $this->makeOrder($this->manager);

        foreach (['confirmed', 'processing', 'completed'] as $status) {
            $this->actingAs($this->manager)
                 ->patch(route('orders.status', $order), ['status' => $status])
                 ->assertRedirect();
        }

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $order = $this->makeOrder($this->manager);
        $order->update(['status' => 'completed']);

        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'pending'])
             ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    // ── Cancellation ──────────────────────────────────────────────────────────

    public function test_manager_can_cancel_pending_order(): void
    {
        $order = $this->makeOrder($this->manager);

        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'cancelled'])
             ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancelling_order_restores_inventory(): void
    {
        $product = $this->productWithStock(100);
        $order   = $this->orderService->createOrder([
            'customer_name'   => 'Cancel Customer',
            'discount_amount' => 0,
            'tax_rate'        => 0,
            'items'           => [['product_id' => $product->id, 'quantity' => 15]],
        ], $this->manager);

        // After create: 100 - 15 = 85
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 85]);

        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'cancelled'])
             ->assertRedirect();

        // After cancel: 85 + 15 = 100 restored
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 100]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id'      => $product->id,
            'type'            => 'in',
            'quantity'        => 15,
            'after_quantity'  => 100,
        ]);
    }

    public function test_completed_order_cannot_be_cancelled(): void
    {
        $order = $this->makeOrder($this->manager);
        $order->update(['status' => 'completed']);

        // Policy denies cancel when isCancellable() is false → 403
        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'cancelled'])
             ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_cancellation_records_who_cancelled_and_when(): void
    {
        $order = $this->makeOrder($this->manager);

        $this->actingAs($this->manager)
             ->patch(route('orders.status', $order), ['status' => 'cancelled']);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status->value);
        $this->assertEquals($this->manager->id, $order->cancelled_by_id);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_employee_cannot_cancel_order(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->employee)
             ->patch(route('orders.status', $order), ['status' => 'cancelled'])
             ->assertForbidden();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_employee_cannot_delete_order(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->employee)
             ->delete(route('orders.destroy', $order))
             ->assertForbidden();
    }

    public function test_admin_can_delete_order(): void
    {
        $order = $this->makeOrder($this->admin);

        $this->actingAs($this->admin)
             ->delete(route('orders.destroy', $order))
             ->assertRedirect(route('orders.index'));

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
