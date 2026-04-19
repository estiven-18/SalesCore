<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Incluye usuarios activos y con borrado logico.
        $totalUsers = User::withTrashed()->count();
        $disabledUsers = User::withTrashed()->where('active', false)->count();
        $sellerUsers = User::withTrashed()->whereHas('roles', function ($query): void {
            $query->whereRaw('LOWER(name) = ?', ['vendedor']);
        })->count();

        return [
            Stat::make('Total Users', (string) $totalUsers),
            Stat::make('Disabled Users', (string) $disabledUsers),
            Stat::make('Number of Sellers', (string) $sellerUsers),
        ];
    }
}