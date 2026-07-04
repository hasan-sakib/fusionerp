<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order, deducting inventory atomically.
     * Throws InsufficientStockException if any product lacks stock.
     */
    public function createOrder(array $data, User $placedBy): Order
    {
        return DB::transaction(function () use ($data, $placedBy) {
            $order = Order::create([
                'order_number'    => 'PENDING',
                'user_id'         => $placedBy->id,
                'customer_name'   => $data['customer_name'],
                'customer_email'  => $data['customer_email'] ?? null,
                'customer_phone'  => $data['customer_phone'] ?? null,
                'status'          => OrderStatus::Pending,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'notes'           => $data['notes'] ?? null,
            ]);

            $order->update([
                'order_number' => sprintf('ORD-%s-%05d', now()->format('Ym'), $order->id),
            ]);

            $subtotal = $this->createItems($order, $data['items'], $placedBy);
            $this->recalculateTotals($order, $subtotal);

            return $order->fresh(['items']);
        });
    }

    /**
     * Update a pending order's items and details, reconciling inventory.
     * Only valid for orders in 'pending' status.
     */
    public function updateOrder(Order $order, array $data, User $updatedBy): void
    {
        DB::transaction(function () use ($order, $data, $updatedBy) {
            // Restore inventory from current items before replacing
            $this->restoreInventory($order, $updatedBy, 'Inventory restored — order ' . $order->order_number . ' edited.');
            $order->items()->delete();

            // Update order metadata
            $order->update([
                'customer_name'   => $data['customer_name'],
                'customer_email'  => $data['customer_email'] ?? null,
                'customer_phone'  => $data['customer_phone'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'notes'           => $data['notes'] ?? null,
            ]);

            $subtotal = $this->createItems($order, $data['items'], $updatedBy);
            $this->recalculateTotals($order, $subtotal);
        });
    }

    /**
     * Transition an order to a new status, enforcing valid state machine transitions.
     * Automatically restores inventory when cancelling.
     */
    public function transitionStatus(Order $order, string $newStatus, User $by, ?string $notes = null): void
    {
        if (!$order->status->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$order->status->value}' to '{$newStatus}'.",
            ]);
        }

        if ($newStatus === 'cancelled') {
            $this->cancelOrder($order, $by, $notes);
        } else {
            $order->update(array_filter([
                'status' => $newStatus,
                'notes'  => $notes ?? $order->notes,
            ]));
        }
    }

    private function cancelOrder(Order $order, User $by, ?string $notes): void
    {
        DB::transaction(function () use ($order, $by, $notes) {
            $this->restoreInventory($order, $by, "Stock restored — order {$order->order_number} cancelled.");

            $order->update([
                'status'          => OrderStatus::Cancelled,
                'cancelled_at'    => now(),
                'cancelled_by_id' => $by->id,
                'notes'           => $notes ?? $order->notes,
            ]);
        });
    }

    private function restoreInventory(Order $order, User $by, string $notes): void
    {
        foreach ($order->items()->with('product')->get() as $item) {
            if (!$item->product) {
                continue;
            }

            $product = Product::lockForUpdate()->find($item->product->id);
            $before  = $product->stock_quantity;
            $after   = $before + $item->quantity;

            $product->update(['stock_quantity' => $after]);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'user_id'         => $by->id,
                'type'            => 'in',
                'quantity'        => $item->quantity,
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'notes'           => $notes,
            ]);
        }
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    private function createItems(Order $order, array $items, User $by): float
    {
        $subtotal = 0.0;

        foreach ($items as $line) {
            $product = Product::lockForUpdate()->findOrFail((int) $line['product_id']);
            $qty     = (int) $line['quantity'];

            if ($product->stock_quantity < $qty) {
                throw new InsufficientStockException($product, $qty, $product->stock_quantity);
            }

            $unitPrice  = (float) $product->price;
            $totalPrice = $unitPrice * $qty;
            $subtotal  += $totalPrice;

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'sku'          => $product->sku,
                'quantity'     => $qty,
                'unit_price'   => $unitPrice,
                'total_price'  => $totalPrice,
            ]);

            $before = $product->stock_quantity;
            $after  = $before - $qty;
            $product->update(['stock_quantity' => $after]);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'user_id'         => $by->id,
                'type'            => 'out',
                'quantity'        => $qty,
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'notes'           => "Reserved for order {$order->order_number}",
            ]);
        }

        return $subtotal;
    }

    private function recalculateTotals(Order $order, float $subtotal): void
    {
        $taxRate    = (float) $order->tax_rate;
        $discount   = (float) $order->discount_amount;
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $total      = max(0.0, $subtotal + $taxAmount - $discount);

        $order->update([
            'subtotal'     => round($subtotal, 2),
            'tax_amount'   => $taxAmount,
            'total_amount' => $total,
        ]);
    }
}
