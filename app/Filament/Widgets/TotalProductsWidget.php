<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalProductsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected function getStats(): array
    {
        $total = Product::query()->count();

        return [
            Stat::make('Total Products', number_format($total))
                ->description('All products in inventory')
                ->icon('heroicon-o-archive-box')
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
