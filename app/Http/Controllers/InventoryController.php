<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryRequest;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with('category')
            ->select('products.*');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level')
                  ->where('min_stock_level', '>', 0);
        }

        if ($request->boolean('out_of_stock')) {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_products'    => Product::count(),
            'low_stock_count'   => Product::whereColumn('stock_quantity', '<=', 'min_stock_level')->where('min_stock_level', '>', 0)->count(),
            'out_of_stock_count' => Product::where('stock_quantity', '<=', 0)->count(),
        ];

        return view('inventory.index', compact('products', 'stats'));
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $movements = $product->inventoryMovements()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('inventory.show', compact('product', 'movements'));
    }

    public function adjust(AdjustInventoryRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $adjustmentType = $request->string('adjustment_type')->toString();
        $quantity = $request->integer('quantity');
        $notes = $request->string('notes')->trim()->toString() ?: null;

        DB::transaction(function () use ($product, $adjustmentType, $quantity, $notes) {
            $before = $product->stock_quantity;

            $after = match ($adjustmentType) {
                'add'      => $before + $quantity,
                'subtract' => max(0, $before - $quantity),
                'set'      => $quantity,
            };

            $movementType = match ($adjustmentType) {
                'add'      => 'in',
                'subtract' => 'out',
                'set'      => 'adjustment',
            };

            $product->update(['stock_quantity' => $after]);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'type'            => $movementType,
                'quantity'        => abs($after - $before),
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'notes'           => $notes,
            ]);
        });

        return redirect()->route('inventory.show', $product)
            ->with('success', 'Stock adjusted successfully.');
    }

    public function movements(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = InventoryMovement::with(['product', 'user'])->latest();

        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        $movements = $query->paginate(30)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('inventory.movements', compact('movements', 'products'));
    }
}
