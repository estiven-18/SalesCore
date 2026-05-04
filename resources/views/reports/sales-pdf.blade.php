<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; }
        h1   { font-size: 18px; margin-bottom: 4px; }
        p.sub { color: #6b7280; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th    { background: #1e40af; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td    { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .section-title { margin-top: 24px; font-size: 14px; font-weight: bold; color: #1e40af; }
        .total-row td  { font-weight: bold; background: #eff6ff; }
    </style>
</head>
<body>

    <h1>Reporte de Ventas</h1>
    <p class="sub">
        Período: {{ $start ? \Carbon\Carbon::parse($start)->format('d/m/Y') : 'Inicio' }}
        al {{ $end ? \Carbon\Carbon::parse($end)->format('d/m/Y') : now()->format('d/m/Y') }}
        — Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    {{-- Resumen --}}
    <div class="section-title">Resumen</div>
    <table>
        <tr>
            <th>Total ventas</th>
            <th>Ingresos totales</th>
            <th>Ticket promedio</th>
        </tr>
        <tr class="total-row">
            <td>{{ $sales->count() }}</td>
            <td>${{ number_format($sales->sum('total'), 2) }}</td>
            <td>${{ number_format($sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0, 2) }}</td>
        </tr>
    </table>

    {{-- Top productos --}}
    <div class="section-title">Productos más vendidos</div>
    <table>
        <tr>
            <th>#</th><th>Producto</th><th>Unidades</th><th>Revenue</th>
        </tr>
        @foreach ($topProducts as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product?->name ?? 'N/A' }}</td>
            <td>{{ number_format($item->total_sold) }}</td>
            <td>${{ number_format($item->total_revenue, 2) }}</td>
        </tr>
        @endforeach
    </table>

    {{-- Detalle ventas --}}
    <div class="section-title">Detalle de ventas</div>
    <table>
        <tr>
            <th>#</th><th>Cliente</th><th>Fecha</th><th>Items</th><th>Subtotal</th><th>IVA</th><th>Total</th>
        </tr>
        @foreach ($sales as $sale)
        <tr>
            <td>{{ $sale->id }}</td>
            <td>{{ $sale->customer?->name ?? 'N/A' }}</td>
            <td>{{ $sale->created_at->format('d/m/Y') }}</td>
            <td>{{ $sale->items->count() }}</td>
            <td>${{ number_format($sale->subtotal, 2) }}</td>
            <td>${{ number_format($sale->tax_total, 2) }}</td>
            <td>${{ number_format($sale->total, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="6">Total</td>
            <td>${{ number_format($sales->sum('total'), 2) }}</td>
        </tr>
    </table>

</body>
</html>