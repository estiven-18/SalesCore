<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AlertStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAlerts = Alert::count();
        //la categoria con mas alertas
        $categoryWithMostAlerts = \App\Models\Category::withCount(['products as alert_count' => function ($query) {
            $query->whereHas('alerts');
        }])
            ->orderBy('alert_count', 'desc')
            ->first();

        return [
            Stat::make('Total Alerts', (string) $totalAlerts),
            Stat::make('The Most Alerted Category', $categoryWithMostAlerts?->name ?? 'None'),
        ];
    }
}
