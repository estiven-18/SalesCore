<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\Icons\Heroicon;

class ShopKpiStats extends BaseWidget
{
    use InteractsWithPageFilters;


    //esto es para calcular la tendencia de cada widget, para cambiar el color dependiendo si sube, bajo o se mantiene
    private function trendColor(array $data, bool $invertido = false): string
    {
        if (count($data) < 2) return 'gray';

        $mitad   = (int) floor(count($data) / 2);
        $primera = array_sum(array_slice($data, 0, $mitad));
        $segunda = array_sum(array_slice($data, $mitad));

        $subiendo = $segunda >= $primera;
        if ($invertido) $subiendo = !$subiendo;

        return $subiendo ? 'success' : 'danger';
    }

    //esto es para para mostrar el porcentaje de cada widget con flechas arriba o abajo
    private function trendDescription(array $data, string $label): string
    {
        if (count($data) < 2) return $label;

        $ultimo    = end($data);
        $penultimo = $data[count($data) - 2];

        if ($penultimo == 0) return $label;

        $pct    = round((($ultimo - $penultimo) / $penultimo) * 100, 1);
        $flecha = $pct >= 0 ? '↑' : '↓';

        return "{$label} ({$flecha} {$pct}%)";
    }


    //esto es para obtener los filtros del dashboard
    private function startDate(): ?string
    {
        return !empty($this->filters['startDate']) ? $this->filters['startDate'] : null;
    }

    private function endDate(): ?string
    {
        return !empty($this->filters['endDate']) ? $this->filters['endDate'] : null;
    }

    private function category(): ?string
    {
        return !empty($this->filters['category']) ? $this->filters['category'] : null;
    }


    //esto es para la consulta de los widgets
    private function baseQuery()
    {
        return Sale::query()
            ->when($this->startDate(), fn($q) => $q->whereDate('created_at', '>=', $this->startDate()))
            ->when($this->endDate(),   fn($q) => $q->whereDate('created_at', '<=', $this->endDate()))
            ->when($this->category(),  fn($q) => $q->whereHas(
                'items.product.categories',
                fn($q2) =>
                $q2->where('categories.id', $this->category())
            ));
    }


    //esto es para construir los datos que salen en los charts
    private function buildCharts(): array
    {
        $months = collect(range(0, 11))->map(
            fn($i) => now()->subMonths(11 - $i)->startOfMonth()
        );

        $allSales = Sale::query()
            ->when($this->category(), fn($q) => $q->whereHas(
                'items.product.categories',
                fn($q2) =>
                $q2->where('categories.id', $this->category())
            ))
            ->get();

        $allItems = SaleItem::all();

        $repeatChart   = [];
        $avgItemsChart = [];
        $revenueChart  = [];

        foreach ($months as $month) {
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            $monthSales = $allSales->filter(
                fn($s) => $s->created_at?->between($start, $end)
            );

            $monthCount      = $monthSales->count();
            $monthRevenue    = (float) $monthSales->sum('total');
            $uniqueCustomers = $monthSales->pluck('customer_id')->unique()->count();

            $repeatCount = $monthSales
                ->groupBy('customer_id')
                ->filter(fn($group) => $group->count() > 1)
                ->count();

            $monthSaleIds = $monthSales->pluck('id')->toArray();
            $monthItems   = $allItems
                ->filter(fn($item) => in_array($item->sale_id, $monthSaleIds))
                ->sum('quantity');

            $repeatChart[] = $uniqueCustomers > 0
                ? round(($repeatCount / $uniqueCustomers) * 100, 1)
                : 0;

            $avgItemsChart[] = $monthCount > 0
                ? round($monthItems / $monthCount, 1)
                : 0;

            $revenueChart[] = $uniqueCustomers > 0
                ? round($monthRevenue / $uniqueCustomers, 2)
                : 0;
        }

        return compact('repeatChart', 'avgItemsChart', 'revenueChart');
    }


    //esto es para obtener las estadísticas de cada widget
    protected function getStats(): array
    {
        $totalOrders  = (clone $this->baseQuery())->count();
        $totalRevenue = (clone $this->baseQuery())->sum('total');

        $uniqueCustomers = (clone $this->baseQuery())
            ->distinct('customer_id')
            ->count('customer_id');

        $repeatCustomers = (clone $this->baseQuery())
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $totalItems = SaleItem::whereHas('sale', function ($q) {
            $q->when($this->startDate(), fn($q) => $q->whereDate('created_at', '>=', $this->startDate()))
                ->when($this->endDate(),   fn($q) => $q->whereDate('created_at', '<=', $this->endDate()))
                ->when($this->category(),  fn($q) => $q->whereHas(
                    'items.product.categories',
                    fn($q2) =>
                    $q2->where('categories.id', $this->category())
                ));
        })->sum('quantity');

        $repeatRate       = $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 1) : 0;
        $avgItemsPerOrder = $totalOrders > 0 ? round($totalItems / $totalOrders, 1) : 0;
        $revPerCustomer   = $uniqueCustomers > 0 ? round($totalRevenue / $uniqueCustomers, 2) : 0;

        ['repeatChart' => $repeatChart, 'avgItemsChart' => $avgItemsChart, 'revenueChart' => $revenueChart]
            = $this->buildCharts();

        return [
            Stat::make('Repeat Customer Rate', $repeatRate . '%')
                ->description($this->trendDescription($repeatChart, $repeatCustomers . ' repeat customers'))
                ->descriptionIcon(Heroicon::ArrowPath)
                ->chart($repeatChart)
                ->color($this->trendColor($repeatChart)),

            Stat::make('Avg Items / Order', (string) $avgItemsPerOrder)
                ->description($this->trendDescription($avgItemsChart, $totalItems . ' items, ' . $totalOrders . ' orders'))
                ->descriptionIcon(Heroicon::ShoppingCart)
                ->chart($avgItemsChart)
                ->color('info'),

            Stat::make('Revenue / Customer', '$' . number_format($revPerCustomer, 2))
                ->description($this->trendDescription($revenueChart, '$' . number_format($totalRevenue, 0) . ' total'))
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->chart($revenueChart)
                ->color($this->trendColor($revenueChart)),
        ];
    }
}
