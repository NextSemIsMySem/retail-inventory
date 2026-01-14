<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatusWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected function getStats(): array
    {
        $activeCount = Product::query()
            ->where('status', 'active')
            ->count();

        $discontinuedCount = Product::query()
            ->where('status', 'discontinued')
            ->count();

        return [
            Stat::make(
                'Active Products',
                number_format($activeCount)
            )
                ->description('Sellable products')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->url(ProductResource::getUrl('index')),

            Stat::make(
                'Discontinued',
                number_format($discontinuedCount)
            )
                ->description('No longer sold')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
