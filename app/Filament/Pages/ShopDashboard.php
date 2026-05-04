<?php

namespace App\Filament\Pages;

use App\Exports\SalesReportExport;
use App\Exports\TopProductsExport;
use App\Filament\Widgets\CustomerGrowthChart;
use App\Filament\Widgets\SalesYearOverYearChart;
use App\Filament\Widgets\ShopKpiStats;
use App\Filament\Widgets\TopProductsChart;
use App\Filament\Widgets\TopProductsWidget;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

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
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                        Select::make('category')
                            ->label('Categoría')
                            ->options(fn(): array => \App\Models\Category::pluck('name', 'id')->all())
                            ->searchable(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Exportar Excel ventas
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('success')
                ->action(function () {
                    $filters  = $this->filters ?? [];
                    $start    = $filters['startDate'] ?? null;
                    $end      = $filters['endDate']   ?? null;
                    $category = $filters['category']  ?? null;
                    $filename = 'reporte-ventas-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new SalesReportExport($start, $end, $category),
                        $filename
                    );
                }),

            // Exportar productos más vendidos en Excel
            Action::make('exportTopProducts')
                ->label('Top Productos Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('info')
                ->action(function () {
                    $filters  = $this->filters ?? [];
                    $filename = 'top-productos-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new TopProductsExport(
                            $filters['startDate'] ?? null,
                            $filters['endDate']   ?? null,
                        ),
                        $filename
                    );
                }),

            // Exportar PDF resumen de ventas y top productos
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('danger')
                ->action(function () {
                    $filters  = $this->filters ?? [];
                    $start    = $filters['startDate'] ?? null;
                    $end      = $filters['endDate']   ?? null;
                    $category = $filters['category']  ?? null;

                    $sales = \App\Models\Sale::query()
                        ->with(['customer', 'items.product'])
                        ->where('active', 1)
                        ->when($start,    fn($q) => $q->whereDate('created_at', '>=', $start))
                        ->when($end,      fn($q) => $q->whereDate('created_at', '<=', $end))
                        ->when($category, fn($q) => $q->whereHas(
                            'items.product.categories',
                            fn($q2) =>
                            $q2->where('categories.id', $category)
                        ))
                        ->orderByDesc('created_at')
                        ->get();

                    $topProducts = \App\Models\SaleItem::query()
                        ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue')
                        ->whereHas(
                            'sale',
                            fn($q) => $q->where('active', 1)
                                ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
                                ->when($end,   fn($q) => $q->whereDate('created_at', '<=', $end))
                        )
                        ->with('product')
                        ->groupBy('product_id')
                        ->orderByDesc('total_sold')
                        ->limit(5)
                        ->get();

                    $pdf = Pdf::loadView('reports.sales-pdf', compact(
                        'sales',
                        'topProducts',
                        'start',
                        'end'
                    ))->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        'reporte-ventas-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            ShopKpiStats::class,
            SalesYearOverYearChart::class,
            CustomerGrowthChart::class,
            TopProductsChart::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
