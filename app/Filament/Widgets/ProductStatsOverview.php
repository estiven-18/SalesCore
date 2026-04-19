<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::withTrashed()->count();
        $productsInStock = Product::withTrashed()->where('stock', '>', 0)->count();
        $producDescativados = Product::withTrashed()->where('active', false)->count();

        return [
            Stat::make('Total Products', (string) $totalProducts),
            Stat::make('Products in Stock', (string) $productsInStock),
            Stat::make('Disabled Products', (string) $producDescativados),
        ];
    }
}
