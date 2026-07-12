<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;
    private Tenant $tenant;
    private OrderService $orderService;
    private ReportService $reportService;

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

        $this->orderService  = $this->app->make(OrderService::class);
        $this->reportService = $this->app->make(ReportService::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function productWithStock(int $stock = 50, float $price = 20.00): Product
    {
        return Product::factory()->create([
            'stock_quantity' => $stock,
            'price'          => $price,
            'status'         => 'active',
        ]);
    }

    private function placeOrder(User $user, int $qty = 2, float $price = 20.00): Order
    {
        $product = $this->productWithStock(100, $price);

        return $this->orderService->createOrder([
            'customer_name'   => 'Test Customer',
            'customer_email'  => 'test@example.com',
            'discount_amount' => 0,
            'tax_rate'        => 0,
            'items'           => [['product_id' => $product->id, 'quantity' => $qty]],
        ], $user);
    }

    private function completedOrder(User $user): Order
    {
        $order = $this->placeOrder($user);
        $order->update(['status' => OrderStatus::Completed]);

        return $order;
    }

    // ── Access control ─────────────────────────────────────────────────────────

    public function test_guest_cannot_access_any_report(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
        $this->get(route('reports.sales'))->assertRedirect(route('login'));
        $this->get(route('reports.inventory'))->assertRedirect(route('login'));
    }

    public function test_all_roles_can_view_reports(): void
    {
        foreach ([$this->admin, $this->manager, $this->employee] as $user) {
            $this->actingAs($user)->get(route('reports.index'))->assertOk();
        }
    }

    public function test_employee_cannot_export_reports(): void
    {
        $this->actingAs($this->employee)
            ->get(route('reports.export', 'inventory'))
            ->assertForbidden();
    }

    public function test_admin_can_export_reports(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.export', 'inventory'))
            ->assertOk()
            ->assertHeaderContains('Content-Type', 'text/csv');
    }

    public function test_manager_can_export_reports(): void
    {
        $this->actingAs($this->manager)
            ->get(route('reports.export', 'inventory'))
            ->assertOk();
    }

    // ── Overview report ────────────────────────────────────────────────────────

    public function test_overview_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Reports Overview')
            ->assertSee('Revenue This Month')
            ->assertSee('Orders This Month');
    }

    public function test_overview_stats_reflect_completed_orders(): void
    {
        $this->completedOrder($this->admin);
        $this->completedOrder($this->manager);

        $stats = $this->reportService->getOverviewStats();

        $this->assertSame(2, $stats['orders']['completed']);
        $this->assertGreaterThan(0.0, $stats['revenue']['total']);
    }

    public function test_revenue_trend_returns_12_months(): void
    {
        $trend = $this->reportService->getRevenueTrend(12);

        $this->assertCount(12, $trend);
        $this->assertArrayHasKey('label', $trend[0]);
        $this->assertArrayHasKey('revenue', $trend[0]);
        $this->assertArrayHasKey('count', $trend[0]);
    }

    public function test_revenue_trend_includes_current_month(): void
    {
        $trend   = $this->reportService->getRevenueTrend(12);
        $lastKey = $trend[count($trend) - 1]['period'];

        $this->assertSame(now()->format('Y-m'), $lastKey);
    }

    public function test_orders_by_status_counts_correctly(): void
    {
        $this->completedOrder($this->admin);
        $this->placeOrder($this->manager); // pending

        $byStatus = $this->reportService->getOrdersByStatus();

        $this->assertSame(1, $byStatus['completed'] ?? 0);
        $this->assertSame(1, $byStatus['pending'] ?? 0);
    }

    // ── Sales report ───────────────────────────────────────────────────────────

    public function test_sales_report_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.sales'))
            ->assertOk()
            ->assertSee('Sales Report')
            ->assertSee('Total Revenue');
    }

    public function test_sales_report_respects_date_filter(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.sales', [
                'from' => now()->subDays(7)->format('Y-m-d'),
                'to'   => now()->format('Y-m-d'),
            ]))
            ->assertOk();
    }

    public function test_sales_summary_counts_completed_revenue(): void
    {
        $order = $this->completedOrder($this->admin);

        $summary = $this->reportService->getSalesSummary(
            now()->subDays(1),
            now()->addDay()
        );

        $this->assertSame(1, $summary['completed']);
        $this->assertEqualsWithDelta($order->total_amount, $summary['revenue'], 0.01);
    }

    public function test_sales_summary_does_not_count_pending_revenue(): void
    {
        $this->placeOrder($this->admin); // stays pending

        $summary = $this->reportService->getSalesSummary(
            now()->subDays(1),
            now()->addDay()
        );

        $this->assertSame(1, $summary['total_orders']);
        $this->assertSame(0, $summary['completed']);
        $this->assertEqualsWithDelta(0.0, $summary['revenue'], 0.01);
    }

    public function test_sales_trend_groups_by_day_for_short_range(): void
    {
        $this->completedOrder($this->admin);

        $trend = $this->reportService->getSalesTrend(
            now()->subDays(7),
            now()
        );

        // Each period should be Y-m-d format
        if (! empty($trend)) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $trend[0]['period']);
        }

        $this->assertIsArray($trend);
    }

    public function test_sales_trend_groups_by_month_for_long_range(): void
    {
        $trend = $this->reportService->getSalesTrend(
            now()->subDays(120),
            now()
        );

        if (! empty($trend)) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $trend[0]['period']);
        }

        $this->assertIsArray($trend);
    }

    public function test_top_products_by_revenue_from_sales(): void
    {
        $this->completedOrder($this->admin);

        $top = $this->reportService->getSalesTopProducts(
            now()->subDay(),
            now()->addDay()
        );

        $this->assertCount(1, $top);
        $this->assertGreaterThan(0, $top->first()->revenue);
    }

    public function test_top_customers_by_spending(): void
    {
        $this->completedOrder($this->admin);
        $this->completedOrder($this->admin);

        $customers = $this->reportService->getTopCustomers(
            now()->subDay(),
            now()->addDay()
        );

        $this->assertGreaterThanOrEqual(1, $customers->count());
        $this->assertGreaterThan(0, $customers->first()->total_spent);
    }

    // ── Inventory report ───────────────────────────────────────────────────────

    public function test_inventory_report_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.inventory'))
            ->assertOk()
            ->assertSee('Inventory Report')
            ->assertSee('Out of Stock');
    }

    public function test_inventory_stats_reflect_products(): void
    {
        Product::factory()->create(['stock_quantity' => 10, 'price' => 5.00, 'status' => 'active']);
        Product::factory()->create(['stock_quantity' => 0,  'price' => 10.00, 'status' => 'active']);
        Product::factory()->create(['stock_quantity' => 2,  'min_stock_level' => 5, 'price' => 8.00, 'status' => 'active']);

        $stats = $this->reportService->getInventoryStats();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['out_of_stock']);
        $this->assertSame(1, $stats['low_stock']);
        $this->assertGreaterThan(0.0, $stats['stock_value']);
    }

    public function test_low_stock_products_are_returned(): void
    {
        Product::factory()->create(['name' => 'Empty Product', 'stock_quantity' => 0, 'status' => 'active']);
        Product::factory()->create(['name' => 'Low Product', 'stock_quantity' => 3, 'min_stock_level' => 10, 'status' => 'active']);
        Product::factory()->create(['name' => 'Fine Product', 'stock_quantity' => 100, 'status' => 'active']);

        $low = $this->reportService->getLowStockProducts();

        $names = $low->pluck('name')->toArray();
        $this->assertContains('Empty Product', $names);
        $this->assertContains('Low Product', $names);
        $this->assertNotContains('Fine Product', $names);
    }

    public function test_stock_by_category_groups_correctly(): void
    {
        $cat = Category::factory()->create(['name' => 'Electronics']);
        Product::factory()->create(['category_id' => $cat->id, 'stock_quantity' => 20, 'price' => 50.00, 'status' => 'active']);
        Product::factory()->create(['category_id' => null,      'stock_quantity' => 5,  'price' => 10.00, 'status' => 'active']);

        $byCategory = $this->reportService->getStockByCategory();

        $catNames = $byCategory->pluck('category')->toArray();
        $this->assertContains('Electronics', $catNames);
        $this->assertContains('Uncategorised', $catNames);
    }

    // ── CSV Export ─────────────────────────────────────────────────────────────

    public function test_sales_csv_export_returns_csv_file(): void
    {
        $this->completedOrder($this->admin);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', 'sales'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('sales-report', $response->headers->get('Content-Disposition'));
    }

    public function test_sales_csv_contains_header_row(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', 'sales'));

        $this->assertStringContainsString('Order #', $response->getContent());
        $this->assertStringContainsString('Customer', $response->getContent());
        $this->assertStringContainsString('Order Total', $response->getContent());
    }

    public function test_inventory_csv_export_returns_csv_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', 'inventory'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inventory-report', $response->headers->get('Content-Disposition'));
    }

    public function test_inventory_csv_contains_header_row(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', 'inventory'));

        $this->assertStringContainsString('SKU', $response->getContent());
        $this->assertStringContainsString('Product Name', $response->getContent());
        $this->assertStringContainsString('Stock Value', $response->getContent());
    }

    public function test_unknown_export_type_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.export', 'unknown'))
            ->assertNotFound();
    }

    public function test_sales_csv_date_range_is_respected(): void
    {
        $rows = $this->reportService->buildSalesCsvRows(
            now()->subDays(30),
            now()
        );

        // Header row always present
        $this->assertCount(1, $rows); // only header when no orders
        $this->assertSame('Order #', $rows[0][0]);
    }

    public function test_inventory_csv_includes_all_products(): void
    {
        Product::factory()->count(3)->create(['status' => 'active']);

        $rows = $this->reportService->buildInventoryCsvRows();

        // Header + 3 products
        $this->assertCount(4, $rows);
    }
}
