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
            Stat::make('Cantidad de productos', (string) $totalProducts),
            Stat::make('Productos en stock', (string) $productsInStock),
            Stat::make('Productos desactivados', (string) $producDescativados),
        ];
    }
}
