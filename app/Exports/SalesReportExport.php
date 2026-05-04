<?php
// Este export se encarga de generar un reporte de ventas basado en los filtros seleccionados en el dashboard
// solo ventas activas, con su cliente, fecha, cantidad de items, subtotal, iva, total y estado

namespace App\Exports;

use App\Models\Sale;
use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
        private ?string $category,
    ) {}

    public function collection()
    {
        return Sale::query()
            ->with(['customer', 'items.product'])
            ->where('active', 1)
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate,   fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->category,  fn($q) => $q->whereHas('items.product.categories', fn($q2) =>
                $q2->where('categories.id', $this->category)
            ))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($sale) => [
                $sale->id,
                $sale->customer?->name ?? 'N/A',
                $sale->created_at->format('d/m/Y H:i'),
                $sale->items->count(),
                '$' . number_format($sale->subtotal, 2),
                '$' . number_format($sale->tax_total, 2),
                '$' . number_format($sale->total, 2),
                $sale->active ? 'Activa' : 'Cancelada',
            ]);
    }

    public function headings(): array
    {
        return ['#', 'Cliente', 'Fecha', 'Items', 'Subtotal', 'IVA', 'Total', 'Estado'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ventas';
    }
}