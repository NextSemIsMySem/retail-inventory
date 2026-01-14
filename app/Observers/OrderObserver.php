<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        $originalStatus = $order->getOriginal('status');

        // Deduct stock only when the status *transitions* to completed.
        if ($order->status === 'completed') {
            if ($originalStatus === 'completed') {
                return;
            }

            if (filled($order->stock_deducted_at)) {
                return;
            }

            $order->deductProductStockForCompletion();

            return;
        }

        // Restore stock only when a completed order transitions to cancelled.
        if ($order->status === 'cancelled' && $originalStatus === 'completed') {
            if (blank($order->stock_deducted_at)) {
                return;
            }

            $order->restoreProductStockForCancellation();
        }
    }
}
