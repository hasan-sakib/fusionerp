<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::with(['user', 'items'])->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', Order::class);

        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'stock_quantity']);

        return view('orders.create', compact('products'));
    }

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        try {
            $order = $this->orderService->createOrder(
                $request->validated(),
                auth()->user()
            );
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} created successfully.");
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['user', 'items.product', 'cancelledBy']);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);

        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'stock_quantity']);

        $order->load('items.product');

        return view('orders.edit', compact('order', 'products'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->orderService->updateOrder($order, $request->validated(), auth()->user());
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} updated successfully.");
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $newStatus = $request->string('status')->toString();

        if ($newStatus === 'cancelled') {
            $this->authorize('cancel', $order);
        } else {
            $this->authorize('process', $order);
        }

        try {
            $this->orderService->transitionStatus(
                $order,
                $newStatus,
                auth()->user(),
                $request->string('notes')->trim()->toString() ?: null,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order status updated to '{$newStatus}'.");
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $number = $order->order_number;
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', "Order {$number} removed.");
    }
}
