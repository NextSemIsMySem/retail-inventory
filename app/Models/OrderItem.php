<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'int',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $quantity = (int) ($item->quantity ?? 0);

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be at least 1.',
                ]);
            }

            if ($item->relationLoaded('order')) {
                $order = $item->getRelation('order');
            } else {
                $order = $item->order()->first();
            }

            if ($order && $order->status === 'completed') {
                throw ValidationException::withMessages([
                    'order' => 'Completed orders cannot be modified.',
                ]);
            }

            $productIdChanged = $item->isDirty('product_id');
            $shouldSnapshotPrice = $item->exists ? ($productIdChanged || blank($item->price)) : blank($item->price);

            if (filled($item->product_id)) {
                $product = $item->relationLoaded('product')
                    ? $item->getRelation('product')
                    : $item->product()->first();

                if ($product) {
                    $available = (int) $product->quantity;

                    $alreadyRequestedInOrder = 0;

                    if ($order) {
                        $alreadyRequestedInOrder = (int) $order->items()
                            ->where('product_id', $item->product_id)
                            ->when($item->exists, fn($q) => $q->whereKeyNot($item->getKey()))
                            ->sum('quantity');
                    }

                    $totalRequested = $alreadyRequestedInOrder + $quantity;

                    if ($totalRequested > $available) {
                        throw ValidationException::withMessages([
                            'quantity' => "Requested quantity exceeds available stock ({$available}).",
                        ]);
                    }

                    if ($shouldSnapshotPrice) {
                        $item->price = $product->price;
                    }
                }
            }

            $price = (float) ($item->price ?? 0);
            $item->setAttribute('subtotal', round($price * $quantity, 2));
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
