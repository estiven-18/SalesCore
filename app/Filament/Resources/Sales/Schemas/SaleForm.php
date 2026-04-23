<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ─────────────────────────────────────────
                // STEP 1: Sale Details
                // ─────────────────────────────────────────
                Step::make('Sale Details')
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('email')->required()->email(),
                                        TextInput::make('document')->required(),
                                        TextInput::make('phone')->required(),
                                        TextInput::make('address'),
                                    ]),

                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->default(auth()->user()->id)
                                    ->disabled()
                                    ->dehydrated(),
                            ])->columns(2),

                        Repeater::make('items')
                            ->relationship()
                            ->table([
                                TableColumn::make('Product')->width('40%'),
                                TableColumn::make('Quantity')->width('10%'),
                                TableColumn::make('Discount')->width('10%'),
                                TableColumn::make('Tax Rate')->width('10%'),
                                TableColumn::make('Unit Price')->width('10%'),
                                TableColumn::make('Total')->width('20%'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = Product::find($state);
                                        $set('unit_price', $product?->price ?? 0);
                                        $set('tax_rate', $product?->tax_rate ?? 0);
                                        self::updateItemTotal($set, $get, unitPrice: $product?->price ?? 0);
                                    })
                                    ->required()
                                    ->hiddenLabel(),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => self::updateItemTotal($set, $get))
                                    ->rules([
                                        function (callable $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $productId = $get('product_id');
                                                if (!$productId) return;
                                                $product = \App\Models\Product::find($productId);
                                                if ($product && (int) $value > $product->stock) {
                                                    $fail("Insufficient stock. Only {$product->stock} units available.");
                                                }
                                            };
                                        }
                                    ]),

                                TextInput::make('discount')
                                    ->label('Discount (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->live()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => self::updateItemTotal($set, $get)),

                                TextInput::make('tax_rate')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->live()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => self::updateItemTotal($set, $get)),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->hiddenLabel(),

                                TextInput::make('total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->hiddenLabel(),
                            ])
                            ->addActionLabel('Add to order items')
                            ->live()
                            ->afterStateUpdated(fn(callable $set, callable $get) => self::updateTotal($set, $get)),
                    ]),

                // ─────────────────────────────────────────
                // STEP 2: Billing
                // ─────────────────────────────────────────
                Step::make('Billing')
                    ->schema([
                        // Resumen visual
                        TextEntry::make('order_summary')
                            ->label('Order Summary')
                            ->state(function (callable $get): HtmlString {
                                return new HtmlString(self::buildSummaryHtml($get, transparent: true));
                            })
                            ->html(),

                        // Botón exportar PDF
                        SchemaActions::make([
                            Action::make('export_pdf')
                                ->label('Export PDF')
                                ->icon('heroicon-o-document-arrow-down')
                                ->color('gray')
                                ->action(function (callable $get) {
                                    $html = self::buildSummaryHtml($get, transparent: false);
                                    $pdf  = Pdf::loadHTML(self::wrapForPdf($html))
                                        ->setOptions([
                                            'isRemoteEnabled'    => true,
                                            'isHtml5ParserEnabled' => true,
                                            'defaultFont'        => 'inter',
                                        ]);
                                    $name = 'sale-summary-' . now()->format('Ymd-His') . '.pdf';

                                    return response()->streamDownload(
                                        fn() => print($pdf->output()),
                                        $name
                                    );
                                }),
                        ]),
                    ]),

            ])->columnSpanFull(),
        ]);
    }

    // ─────────────────────────────────────────
    // Calcula el total de una fila individual
    // ─────────────────────────────────────────
    protected static function updateItemTotal(callable $set, callable $get, ?float $unitPrice = null): void
    {
        $quantity      = max(0, floatval($get('quantity')   ?? 0));
        $unitPrice     = max(0, floatval($unitPrice ?? $get('unit_price') ?? 0));
        $discount      = min(100, max(0, floatval($get('discount')  ?? 0)));
        $taxRate       = max(0, floatval($get('tax_rate')   ?? 0));

        $base          = $quantity * $unitPrice;
        $afterDiscount = $base * (1 - $discount / 100);
        $total         = $afterDiscount * (1 + $taxRate / 100);

        $set('total', number_format($total, 2, '.', ''));
    }

    // ─────────────────────────────────────────
    // Calcula y guarda los 4 totales del Sale
    // ─────────────────────────────────────────
    protected static function updateTotal(callable $set, callable $get): void
    {
        $items         = $get('items') ?? [];
        $subtotal      = 0;
        $totalDiscount = 0;
        $totalTax      = 0;

        foreach ($items as $item) {
            $qty       = max(0, floatval($item['quantity']   ?? 0));
            $unitPrice = max(0, floatval($item['unit_price'] ?? 0));
            $discount  = min(100, max(0, floatval($item['discount']  ?? 0)));
            $taxRate   = max(0, floatval($item['tax_rate']   ?? 0));

            $base           = $qty * $unitPrice;
            $discountAmount = $base * ($discount / 100);
            $afterDiscount  = $base - $discountAmount;
            $taxAmount      = $afterDiscount * ($taxRate / 100);

            $subtotal      += $afterDiscount;
            $totalDiscount += $discountAmount;
            $totalTax      += $taxAmount;
        }

        $grandTotal = $subtotal + $totalTax;

        $set('subtotal',       number_format($subtotal,      2, '.', ''));
        $set('discount_total', number_format($totalDiscount, 2, '.', ''));
        $set('tax_total',      number_format($totalTax,      2, '.', ''));
        $set('total',          number_format($grandTotal,    2, '.', ''));
    }

    // ─────────────────────────────────────────
    // HTML del resumen (pantalla + PDF)
    // ─────────────────────────────────────────
    protected static function buildSummaryHtml(callable $get, bool $transparent = true): string
    {
        $items         = $get('items') ?? [];
        $subtotal      = 0;
        $totalDiscount = 0;
        $totalTax      = 0;
        $grandTotal    = 0;

        $bg       = $transparent ? 'transparent' : '#ffffff';
        $headerBg = $transparent ? 'transparent' : '#f9fafb';
        $font     = 'Inter, sans-serif';

        $rows = '';
        foreach ($items as $item) {
            $productId      = $item['product_id'] ?? null;
            $product        = $productId ? Product::find($productId) : null;
            $name           = $product?->name ?? '—';

            $qty            = max(0, floatval($item['quantity']   ?? 0));
            $unitPrice      = max(0, floatval($item['unit_price'] ?? 0));
            $discount       = min(100, max(0, floatval($item['discount']  ?? 0)));
            $taxRate        = max(0, floatval($item['tax_rate']   ?? 0));

            $base           = $qty * $unitPrice;
            $discountAmount = $base * ($discount / 100);
            $afterDiscount  = $base - $discountAmount;
            $taxAmount      = $afterDiscount * ($taxRate / 100);
            $lineTotal      = $afterDiscount + $taxAmount;

            $subtotal      += $afterDiscount;
            $totalDiscount += $discountAmount;
            $totalTax      += $taxAmount;
            $grandTotal    += $lineTotal;

            $rows .= "
                <tr style='border-bottom:1px solid #e5e7eb;'>
                    <td style='padding:10px 12px; font-weight:500; font-family:{$font};'>{$name}</td>
                    <td style='padding:10px 12px; text-align:center; font-family:{$font};'>{$qty}</td>
                    <td style='padding:10px 12px; text-align:right; font-family:{$font};'>$ " . number_format($unitPrice, 2) . "</td>
                    <td style='padding:10px 12px; text-align:center; color:#ef4444; font-family:{$font};'>{$discount}%</td>
                    <td style='padding:10px 12px; text-align:right; color:#ef4444; font-family:{$font};'>- $ " . number_format($discountAmount, 2) . "</td>
                    <td style='padding:10px 12px; text-align:center; color:#6b7280; font-family:{$font};'>{$taxRate}%</td>
                    <td style='padding:10px 12px; text-align:right; color:#16a34a; font-weight:600; font-family:{$font};'>$ " . number_format($lineTotal, 2) . "</td>
                </tr>
            ";
        }

        if (empty($items)) {
            $rows = "<tr><td colspan='7' style='padding:20px; text-align:center; color:#9ca3af; font-family:{$font};'>No items added.</td></tr>";
        }

        return "
            <div style='border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; font-size:14px; background:{$bg}; font-family:{$font};'>
                <table style='width:100%; border-collapse:collapse; background:{$bg}; font-family:{$font};'>
                    <thead>
                        <tr style='background:{$headerBg}; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:.05em;'>
                            <th style='padding:10px 12px; text-align:left; font-family:{$font};'>Product</th>
                            <th style='padding:10px 12px; text-align:center; font-family:{$font};'>Qty</th>
                            <th style='padding:10px 12px; text-align:right; font-family:{$font};'>Unit Price</th>
                            <th style='padding:10px 12px; text-align:center; font-family:{$font};'>Disc.</th>
                            <th style='padding:10px 12px; text-align:right; font-family:{$font};'>Discount</th>
                            <th style='padding:10px 12px; text-align:center; font-family:{$font};'>Tax</th>
                            <th style='padding:10px 12px; text-align:right; font-family:{$font};'>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>{$rows}</tbody>
                </table>

                <div style='background:{$headerBg}; padding:16px 20px; border-top:1px solid #e5e7eb; font-family:{$font};'>
                    <div style='display:flex; justify-content:flex-end;'>
                        <div style='min-width:280px; display:flex; flex-direction:column; gap:8px;'>
                            <div style='display:flex; justify-content:space-between; color:#6b7280; font-family:{$font};'>
                                <span>Subtotal</span>
                                <span>$ " . number_format($subtotal, 2) . "</span>
                            </div>
                            <div style='display:flex; justify-content:space-between; color:#ef4444; font-family:{$font};'>
                                <span>Total Discount</span>
                                <span>- $ " . number_format($totalDiscount, 2) . "</span>
                            </div>
                            <div style='display:flex; justify-content:space-between; color:#6b7280; font-family:{$font};'>
                                <span>Total Tax</span>
                                <span>+ $ " . number_format($totalTax, 2) . "</span>
                            </div>
                            <div style='display:flex; justify-content:space-between; font-size:16px; font-weight:700; border-top:2px solid #e5e7eb; padding-top:10px; margin-top:4px; font-family:{$font};'>
                                <span>Grand Total</span>
                                <span style='color:#16a34a;'>$ " . number_format($grandTotal, 2) . "</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }

    // ─────────────────────────────────────────
    // Envuelve el HTML para DomPDF
    // ─────────────────────────────────────────
    protected static function wrapForPdf(string $content): string
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
                    body { font-size: 13px; color: #111827; padding: 30px; background: #ffffff; }
                    h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #111827; }
                </style>
            </head>
            <body>
                <h2>Sale Summary</h2>
                {$content}
            </body>
            </html>
        ";
    }
}