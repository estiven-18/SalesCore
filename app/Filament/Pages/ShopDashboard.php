<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesYearOverYearChart;
use App\Filament\Widgets\CustomerGrowthChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Filament\Widgets\ShopKpiStats;
use Livewire\Component;

class ShopDashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    protected static string $routePath = 'shop';
    protected static ?string $title = 'Shop Dashboard';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static ?int $navigationSort = 2;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                        Select::make('category')
                            ->label('Categoría')
                            ->options(fn (): array => \App\Models\Category::pluck('name', 'id')->all())
                            ->searchable(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            ShopKpiStats::class,
            SalesYearOverYearChart::class,
            CustomerGrowthChart::class,
        ];
    }
}
