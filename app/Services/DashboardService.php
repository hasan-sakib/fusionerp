<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'total_products' => $this->safe(fn () => Product::count()),
            'total_users'    => $this->safe(fn () => User::count()),
            'total_orders'   => $this->safe(fn () => Order::count()),
            'total_revenue'  => $this->safe(fn () => Order::where('status', 'completed')->sum('total_amount')),
        ];
    }

    public function getChartData(): array
    {
        $monthlyRevenue = $this->safe(function () {
            return Order::where('status', 'completed')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as revenue')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month')
                ->toArray();
        }, []);

        $ordersByStatus = $this->safe(function () {
            return Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        }, []);

        $topProducts = $this->safe(function () {
            return DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'completed')
                ->selectRaw('products.name, SUM(order_items.quantity) as total_sold')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->toArray();
        }, []);

        return compact('monthlyRevenue', 'ordersByStatus', 'topProducts');
    }

    private function safe(callable $fn, mixed $default = 0): mixed
    {
        try {
            return $fn();
        } catch (Throwable) {
            return $default;
        }
    }
}
