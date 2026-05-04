<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;


class TopProductsChart  extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Top Products Sold';

    protected function getType(): string
    {
        return 'bar';
    }


    private function startDate(): ?string
    {
        return $this->filters['startDate'] ?? null;
    }

    private function endDate(): ?string
    {
        return $this->filters['endDate'] ?? null;
    }

    private function category(): ?string
    {
        return $this->filters['category'] ?? null;
    }

    protected function getData(): array
    {
        $products = SaleItem::query()
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when(
                $this->startDate(),
                fn($q) =>
                $q->whereHas(
                    'sale',
                    fn($q2) =>
                    $q2->whereDate('created_at', '>=', $this->startDate())
                )
            )
            ->when(
                $this->endDate(),
                fn($q) =>
                $q->whereHas(
                    'sale',
                    fn($q2) =>
                    $q2->whereDate('created_at', '<=', $this->endDate())
                )
            )
            ->when(
                $this->category(),
                fn($q) =>
                $q->whereHas(
                    'product.categories',
                    fn($q2) =>
                    $q2->where('categories.id', $this->category())
                )
            )
            ->selectRaw('products.name as product_name, SUM(sale_items.quantity) as total_quantity')
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return [
            'labels' => $products->pluck('product_name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Cantidad Vendida',
                    'data' => $products->pluck('total_quantity')->map(fn($v) => (int) $v)->toArray(),
                ],
            ],
        ];
    }
}
