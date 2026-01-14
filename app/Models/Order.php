<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'stock_deducted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            // Stock changes are tied to status *transitions* (e.g. pending -> completed).
            // Ensure we never create an order already in a stock-affecting state.
            if ($order->status === 'completed') {
                $order->status = 'pending';
            }

            $order->stock_deducted_at = null;
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal()
    {
        $this->total_amount = $this->items()->sum('subtotal');
        $this->save();
    }

    public function deductProductStockForCompletion(): void
    {
        DB::transaction(function (): void {
            /** @var self $freshOrder */
            $freshOrder = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (filled($freshOrder->stock_deducted_at)) {
                return;
            }

            if ($freshOrder->status !== 'completed') {
                return;
            }

            $items = $freshOrder->items()->get(['product_id', 'quantity']);

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Cannot complete an order with no items.',
                ]);
            }

            $requiredByProduct = $items
                ->groupBy('product_id')
                ->map(fn($group) => (int) $group->sum('quantity'));

            $productIds = $requiredByProduct->keys()->filter()->values();

            $products = Product::query()
                ->whereKey($productIds)
                ->lockForUpdate()
                ->get(['id', 'name', 'quantity'])
                ->keyBy('id');

            $errors = [];

            foreach ($requiredByProduct as $productId => $requiredQty) {
                if ($requiredQty < 1) {
                    $errors["items"] = 'Order item quantity must be at least 1.';
                    continue;
                }

                $product = $products->get($productId);

                if (!$product) {
                    $errors["items"] = 'One or more products in this order no longer exist.';
                    continue;
                }

                $availableQty = (int) $product->quantity;

                if ($requiredQty > $availableQty) {
                    $productLabel = filled($product->name) ? $product->name : (string) $productId;
                    $errors["items"] = "Insufficient stock for {$productLabel}. Requested {$requiredQty}, available {$availableQty}.";
                }
            }

            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            foreach ($requiredByProduct as $productId => $requiredQty) {
                $updated = Product::query()
                    ->whereKey($productId)
                    ->where('quantity', '>=', $requiredQty)
                    ->decrement('quantity', $requiredQty);

                if ($updated !== 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Stock changed while completing the order. Please try again.',
                    ]);
                }
            }

            $freshOrder->forceFill([
                'stock_deducted_at' => now(),
            ])->saveQuietly();
        });
    }

    public function restoreProductStockForCancellation(): void
    {
        DB::transaction(function (): void {
            /** @var self $freshOrder */
            $freshOrder = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($freshOrder->status !== 'cancelled') {
                return;
            }

            if (blank($freshOrder->stock_deducted_at)) {
                return;
            }

            $items = $freshOrder->items()->get(['product_id', 'quantity']);

            foreach ($items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->increment('quantity', (int) $item->quantity);
            }

            $freshOrder->forceFill([
                'stock_deducted_at' => null,
            ])->saveQuietly();
        });
    }
}
