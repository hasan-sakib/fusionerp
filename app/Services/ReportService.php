<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function periodFormat(string $column, string $format): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "strftime('{$format}', {$column})";
        }

        return "DATE_FORMAT({$column}, '{$format}')";
    }

    // -------------------------------------------------------------------------
    // Overview
    // -------------------------------------------------------------------------

    public function getOverviewStats(): array
    {
        $thisMonth    = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd   = now()->subMonth()->endOfMonth();

        $revenue = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->selectRaw(
                'SUM(total_amount) as total,
                 SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END) as this_month,
                 SUM(CASE WHEN created_at BETWEEN ? AND ? THEN total_amount ELSE 0 END) as last_month',
                [$thisMonth, $lastMonthStart, $lastMonthEnd]
            )
            ->first();

        $orders = DB::table('orders')
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month,
                 SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_month,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled',
                [$thisMonth, $lastMonthStart, $lastMonthEnd, 'completed', 'pending', 'cancelled']
            )
            ->first();

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                 SUM(CASE WHEN stock_quantity > 0 AND min_stock_level > 0 AND stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock,
                 SUM(stock_quantity * price) as stock_value'
            )
            ->first();

        return [
            'revenue' => [
                'total'      => (float) ($revenue->total ?? 0),
                'this_month' => (float) ($revenue->this_month ?? 0),
                'last_month' => (float) ($revenue->last_month ?? 0),
                'change_pct' => $this->pctChange((float) ($revenue->last_month ?? 0), (float) ($revenue->this_month ?? 0)),
            ],
            'orders' => [
                'total'      => (int) ($orders->total ?? 0),
                'this_month' => (int) ($orders->this_month ?? 0),
                'last_month' => (int) ($orders->last_month ?? 0),
                'completed'  => (int) ($orders->completed ?? 0),
                'pending'    => (int) ($orders->pending ?? 0),
                'cancelled'  => (int) ($orders->cancelled ?? 0),
                'change_pct' => $this->pctChange((float) ($orders->last_month ?? 0), (float) ($orders->this_month ?? 0)),
            ],
            'products' => [
                'total'       => (int) ($products->total ?? 0),
                'out_of_stock'=> (int) ($products->out_of_stock ?? 0),
                'low_stock'   => (int) ($products->low_stock ?? 0),
                'stock_value' => (float) ($products->stock_value ?? 0),
            ],
            'users' => [
                'total' => DB::table('users')->whereNull('deleted_at')->count(),
            ],
        ];
    }

    public function getRevenueTrend(int $months = 12): array
    {
        $from = now()->subMonths($months - 1)->startOfMonth();

        $rows = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->selectRaw($this->periodFormat('created_at', '%Y-%m') . " as period, SUM(total_amount) as revenue, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        // Fill every month in range, even months with no data
        $periods = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $periods[$key] = [
                'period'  => $key,
                'label'   => now()->subMonths($i)->format('M Y'),
                'revenue' => isset($rows[$key]) ? (float) $rows[$key]->revenue : 0.0,
                'count'   => isset($rows[$key]) ? (int) $rows[$key]->count : 0,
            ];
        }

        return array_values($periods);
    }

    public function getOrdersByStatus(): array
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getTopProductsByRevenue(int $limit = 8): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', 'completed')
            ->selectRaw('order_items.product_name, SUM(order_items.total_price) as revenue, SUM(order_items.quantity) as units_sold')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function getTopCustomers(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('customer_name, customer_email, COUNT(*) as order_count, SUM(total_amount) as total_spent')
            ->groupBy('customer_name', 'customer_email')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Sales report
    // -------------------------------------------------------------------------

    public function getSalesSummary(Carbon $from, Carbon $to): array
    {
        $row = DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw(
                'COUNT(*) as total_orders,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                 SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as revenue,
                 AVG(CASE WHEN status = ? THEN total_amount ELSE NULL END) as avg_value',
                ['completed', 'cancelled', 'completed', 'completed']
            )
            ->first();

        return [
            'total_orders'   => (int) ($row->total_orders ?? 0),
            'completed'      => (int) ($row->completed ?? 0),
            'cancelled'      => (int) ($row->cancelled ?? 0),
            'revenue'        => (float) ($row->revenue ?? 0),
            'avg_order_value'=> (float) ($row->avg_value ?? 0),
        ];
    }

    public function getSalesTrend(Carbon $from, Carbon $to): array
    {
        $days   = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->endOfDay());
        $format = $days <= 60 ? '%Y-%m-%d' : '%Y-%m';

        $rows = DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw($this->periodFormat('created_at', $format) . " as period, SUM(total_amount) as revenue, COUNT(*) as orders")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $rows->map(fn ($r) => [
            'period'  => $r->period,
            'revenue' => (float) $r->revenue,
            'orders'  => (int) $r->orders,
        ])->toArray();
    }

    public function getSalesTopProducts(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as units, SUM(order_items.total_price) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Inventory report
    // -------------------------------------------------------------------------

    public function getInventoryStats(): array
    {
        $row = DB::table('products')
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                 SUM(CASE WHEN stock_quantity > 0 AND min_stock_level > 0 AND stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock,
                 SUM(stock_quantity * price) as stock_value,
                 SUM(stock_quantity) as total_units'
            )
            ->first();

        return [
            'total'       => (int) ($row->total ?? 0),
            'out_of_stock'=> (int) ($row->out_of_stock ?? 0),
            'low_stock'   => (int) ($row->low_stock ?? 0),
            'stock_value' => (float) ($row->stock_value ?? 0),
            'total_units' => (int) ($row->total_units ?? 0),
        ];
    }

    public function getStockByCategory(): Collection
    {
        return DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->selectRaw(
                "COALESCE(categories.name, 'Uncategorised') as category,
                 COUNT(products.id) as product_count,
                 SUM(products.stock_quantity) as total_stock,
                 SUM(products.stock_quantity * products.price) as stock_value"
            )
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('stock_value')
            ->get();
    }

    public function getLowStockProducts(int $limit = 30): Collection
    {
        return DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->where(function ($q): void {
                $q->where('products.stock_quantity', '<=', 0)
                    ->orWhereRaw('products.min_stock_level > 0 AND products.stock_quantity <= products.min_stock_level');
            })
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.stock_quantity',
                'products.min_stock_level',
                'products.price',
                DB::raw("COALESCE(categories.name, 'Uncategorised') as category"),
            ])
            ->orderBy('products.stock_quantity')
            ->limit($limit)
            ->get();
    }

    public function getRecentMovements(int $days = 30): Collection
    {
        return DB::table('inventory_movements')
            ->join('products', 'products.id', '=', 'inventory_movements.product_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_movements.user_id')
            ->where('inventory_movements.created_at', '>=', now()->subDays($days))
            ->select([
                'inventory_movements.id',
                'inventory_movements.type',
                'inventory_movements.quantity',
                'inventory_movements.before_quantity',
                'inventory_movements.after_quantity',
                'inventory_movements.notes',
                'inventory_movements.created_at',
                'products.name as product_name',
                'products.sku as product_sku',
                'users.name as user_name',
            ])
            ->orderByDesc('inventory_movements.created_at')
            ->limit(50)
            ->get();
    }

    // -------------------------------------------------------------------------
    // CSV Export helpers
    // -------------------------------------------------------------------------

    public function buildSalesCsvRows(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select([
                'orders.order_number',
                'orders.created_at',
                'orders.status',
                'orders.customer_name',
                'orders.customer_email',
                'order_items.product_name',
                'order_items.sku',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.total_price',
                'orders.subtotal',
                'orders.tax_amount',
                'orders.discount_amount',
                'orders.total_amount',
            ])
            ->orderBy('orders.created_at')
            ->orderBy('orders.id')
            ->get();

        $lines = [['Order #', 'Date', 'Status', 'Customer', 'Email', 'Product', 'SKU', 'Qty', 'Unit Price', 'Line Total', 'Subtotal', 'Tax', 'Discount', 'Order Total']];
        foreach ($rows as $r) {
            $lines[] = [
                $r->order_number,
                $r->created_at,
                $r->status,
                $r->customer_name,
                $r->customer_email ?? '',
                $r->product_name,
                $r->sku ?? '',
                $r->quantity,
                number_format((float) $r->unit_price, 2, '.', ''),
                number_format((float) $r->total_price, 2, '.', ''),
                number_format((float) $r->subtotal, 2, '.', ''),
                number_format((float) $r->tax_amount, 2, '.', ''),
                number_format((float) $r->discount_amount, 2, '.', ''),
                number_format((float) $r->total_amount, 2, '.', ''),
            ];
        }

        return $lines;
    }

    public function buildInventoryCsvRows(): array
    {
        $rows = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->select([
                'products.sku',
                'products.name',
                DB::raw("COALESCE(categories.name, 'Uncategorised') as category"),
                'products.stock_quantity',
                'products.min_stock_level',
                'products.price',
                DB::raw('products.stock_quantity * products.price as stock_value'),
                'products.status',
            ])
            ->orderBy('products.name')
            ->get();

        $lines = [['SKU', 'Product Name', 'Category', 'Stock Qty', 'Min Stock Level', 'Unit Price', 'Stock Value', 'Status']];
        foreach ($rows as $r) {
            $lines[] = [
                $r->sku ?? '',
                $r->name,
                $r->category,
                $r->stock_quantity,
                $r->min_stock_level,
                number_format((float) $r->price, 2, '.', ''),
                number_format((float) $r->stock_value, 2, '.', ''),
                ucfirst($r->status),
            ];
        }

        return $lines;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function pctChange(float $old, float $new): float
    {
        if ($old == 0.0) {
            return $new > 0 ? 100.0 : 0.0;
        }

        return round((($new - $old) / $old) * 100, 1);
    }
}
