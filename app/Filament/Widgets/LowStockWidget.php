<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected function getStats(): array
    {
        $lowStockCount = Product::query()
            ->where('quantity', '<', 10)
            ->count();

        $hasLowStock = $lowStockCount > 0;

        return [
            Stat::make(
                'Low Stock Items',
                number_format($lowStockCount)
            )
                ->description('Quantity < 10')
                ->color($hasLowStock ? 'danger' : 'success')
                ->icon($hasLowStock ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
