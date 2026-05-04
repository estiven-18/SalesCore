<?php
// Este export se encarga de generar un reporte de los productos más vendidos basado en los filtros seleccionados en el dashboard
//es mas como un resumen de excel de ventas y del excel de productos mas vendidos

namespace App\Exports;

use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopProductsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
    ) {}

    public function collection()
    {
        return SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue')
            ->whereHas('sale', function ($q) {
                $q->where('active', 1)
                  ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                  ->when($this->endDate,   fn($q) => $q->whereDate('created_at', '<=', $this->endDate));
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->get()
            ->map(fn($item) => [
                $item->product?->name ?? 'N/A',
                $item->product?->description ?? '',
                '$' . number_format($item->product?->price ?? 0, 2),
                $item->total_sold,
                '$' . number_format($item->total_revenue, 2),
            ]);
    }

    public function headings(): array
    {
        return ['Producto', 'Descripción', 'Precio Unit.', 'Unidades Vendidas', 'Revenue Total'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Productos más vendidos';
    }
}