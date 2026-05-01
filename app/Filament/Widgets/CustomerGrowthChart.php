<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class CustomerGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Customer Growth';
    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'line';
    }

    //esto es para obtner los datos del chart
    protected function getData(): array
    {
        $startDate = !empty($this->filters['startDate'])
            ? Carbon::parse($this->filters['startDate'])->startOfMonth()
            : Carbon::now()->subMonths(11)->startOfMonth();

        $endDate = !empty($this->filters['endDate'])
            ? Carbon::parse($this->filters['endDate'])->endOfMonth()
            : now();

        $months = collect();
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $months->push($cursor->copy());
            $cursor->addMonth();
        }

        $customers = Customer::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get(['created_at'])
            ->groupBy(fn(Customer $c): string => $c->created_at?->format('Y-m') ?? '')
            ->map(fn($group) => $group->count());

        $totalBefore = Customer::where('created_at', '<', $startDate)->count();

        $labels          = [];
        $newData         = [];
        $cumulativeData  = [];
        $cumulative      = $totalBefore;

        foreach ($months as $month) {
            $key         = $month->format('Y-m');
            $newThisMonth = $customers->get($key, 0);
            $cumulative  += $newThisMonth;

            $labels[]         = $month->format('M Y');
            $newData[]        = $newThisMonth;
            $cumulativeData[] = $cumulative;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'New Customers',
                    'data'            => $newData,
                    'fill'            => 'start',
                    'borderColor'     => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
            ],
            'labels' => $labels,
        ];
    }

    //se puede configurar las opciones del chart (estilos)
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend'  => ['display' => true],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position'    => 'left',
                    'ticks'       => ['stepSize' => 1],
                ],
            ],
        ];
    }
}