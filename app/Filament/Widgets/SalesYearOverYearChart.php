<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class SalesYearOverYearChart extends ChartWidget
{
    use InteractsWithPageFilters;

    //esto es para mostrar el titulo del widget, y el orden dentro del dashboard
    protected ?string $heading = 'Sales year over year';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    //esto es para obtner los datos del chart
    protected function getData(): array
    {
        $category  = !empty($this->filters['category'])  ? $this->filters['category']  : null;
        $startDate = !empty($this->filters['startDate']) ? $this->filters['startDate'] : null;
        $endDate   = !empty($this->filters['endDate'])   ? $this->filters['endDate']   : null;

        $recentMonths = collect(range(11, 0))->map(
            fn(int $ago) => Carbon::now()->subMonths($ago)->startOfMonth()
        );
        $priorMonths = collect(range(23, 12))->map(
            fn(int $ago) => Carbon::now()->subMonths($ago)->startOfMonth()
        );

        $recentStart = $recentMonths->first();
        $priorStart  = $priorMonths->first();

        //estos son los filtros
        $baseQuery = fn() => Sale::query()
            ->where('active', 1)
            ->when($category, fn($q) => $q->whereHas('items.product.categories', fn($q2) =>
                $q2->where('categories.id', $category)
            ))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('created_at', '<=', $endDate));

        $recentOrders = $baseQuery()
            ->where('created_at', '>=', $recentStart)
            ->get(['created_at'])
            ->groupBy(fn(Sale $s): string => $s->created_at?->format('Y-m') ?? '')
            ->map(fn($group) => $group->count());

        $priorOrders = $baseQuery()
            ->where('created_at', '>=', $priorStart)
            ->where('created_at', '<',  $recentStart)
            ->get(['created_at'])
            ->groupBy(fn(Sale $s): string => $s->created_at?->format('Y-m') ?? '')
            ->map(fn($group) => $group->count());

        $labels     = [];
        $recentData = [];
        $priorData  = [];

        foreach ($recentMonths as $month) {
            $labels[]     = $month->format('M Y');
            $recentData[] = $recentOrders->get($month->format('Y-m'), 0);
        }

        foreach ($priorMonths as $month) {
            $priorData[] = $priorOrders->get($month->format('Y-m'), 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Last 12 months',
                    'data'            => $recentData,
                    'borderColor'     => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill'            => 'start',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }


    //esto es para configurar las opciones del chart
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
            'interaction' => ['mode' => 'nearest', 'axis' => 'x', 'intersect' => false],
        ];
    }
}