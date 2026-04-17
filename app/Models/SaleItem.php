<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleItem extends Model
{
    use SoftDeletes;

    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'active',
    ];

    protected static function booted(): void
    {
        static::saving(function (SaleItem $item) {
            $item->calculateAmounts();
        });

        static::saved(function (SaleItem $item) {
            $item->sale?->recalculateTotalsFromItems();
        });

        static::deleted(function (SaleItem $item) {
            $item->sale?->recalculateTotalsFromItems();
        });
    }

    protected function calculateAmounts(): void
    {
        $product = Product::find($this->product_id);

        if ($product) {
            // Preserve historical price by storing it on the sale item at sale time.
            $this->unit_price = (float) $product->price;

            if ($this->tax_rate === null) {
                $this->tax_rate = (float) $product->tax_rate;
            }
        }

        if ($this->quantity === null || $this->quantity === '') {
            $this->quantity = 1;
        }

        if ($this->discount === null || $this->discount === '') {
            $this->discount = 0;
        }

        $quantity = max(0, (float) $this->quantity);
        $unitPrice = max(0, (float) ($this->unit_price ?? 0));
        $discountRate = min(100, max(0, (float) $this->discount));
        $taxRate = max(0, (float) ($this->tax_rate ?? 0));

        // 1) subtotal_bruto = precio * cantidad
        $subtotalBruto = $quantity * $unitPrice;

        // 2) descuento_aplicado (discount se interpreta como %)
        $descuentoAplicado = $subtotalBruto * ($discountRate / 100);

        // 3) subtotal = subtotal_bruto - descuento
        $subtotalSinImpuesto = max(0, $subtotalBruto - $descuentoAplicado);

        // 4) impuesto = subtotal * (tax / 100)
        $impuesto = $subtotalSinImpuesto * ($taxRate / 100);

        // 5) total = subtotal + impuesto (se usa en la venta resumen)
        $this->tax_amount = round($impuesto, 2);
        $this->subtotal = round($subtotalSinImpuesto, 2);
        $this->active = $this->active ?? true;
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}