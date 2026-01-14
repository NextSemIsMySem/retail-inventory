<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    protected function getStats(): array
    {
        $totalOrders = Order::query()->count();

        $completedOrders = Order::query()
            ->where('status', 'completed')
            ->count();

        $pendingOrders = Order::query()
            ->where('status', 'pending')
            ->count();

        $ordersIndexUrl = OrderResource::getUrl('index');

        return [
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All orders')
                ->icon('heroicon-o-receipt-refund')
                ->color('gray')
                ->url($ordersIndexUrl),

            Stat::make('Completed Orders', number_format($completedOrders))
                ->description('Status: completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->url($ordersIndexUrl),

            Stat::make('Pending Orders', number_format($pendingOrders))
                ->description('Status: pending')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url($ordersIndexUrl),
        ];
    }
}
