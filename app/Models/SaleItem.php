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
        // Al crear un ítem → descontar stock
        static::created(function (SaleItem $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->decrement('stock', (int) $item->quantity);
                $product->refresh();
                $product->checkLowStockAlert();
            }
        });

        // Al actualizar → ajustar la diferencia de stock
        static::updated(function (SaleItem $item) {
            if ($item->wasChanged('quantity') || $item->wasChanged('product_id')) {
                // Devolver stock del producto anterior
                $oldProductId = $item->getOriginal('product_id');
                $oldQty       = (int) $item->getOriginal('quantity');
                $oldProduct   = Product::find($oldProductId);
                if ($oldProduct) {
                    $oldProduct->increment('stock', $oldQty);
                }

                // Descontar stock del producto nuevo
                $newProduct = Product::find($item->product_id);
                if ($newProduct) {
                    $newProduct->decrement('stock', (int) $item->quantity);
                    $newProduct->refresh();
                    $newProduct->checkLowStockAlert();
                }
            }
        });

        // Al eliminar (soft delete) → devolver stock
        static::deleted(function (SaleItem $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', (int) $item->quantity);
            }
            $item->sale?->recalculateTotalsFromItems();
        });

        static::saving(function (SaleItem $item) {
            $item->calculateAmounts();
        });

        static::saved(function (SaleItem $item) {
            $item->sale?->recalculateTotalsFromItems();
        });
    }

    protected function calculateAmounts(): void
    {
        $product = Product::find($this->product_id);

        if ($product) {
            $this->unit_price = (float) $product->price;
            if ($this->tax_rate === null) {
                $this->tax_rate = (float) $product->tax_rate;
            }
        }

        if ($this->quantity === null || $this->quantity === '') $this->quantity = 1;
        if ($this->discount === null || $this->discount === '') $this->discount = 0;

        $quantity      = max(0, (float) $this->quantity);
        $unitPrice     = max(0, (float) ($this->unit_price ?? 0));
        $discountRate  = min(100, max(0, (float) $this->discount));
        $taxRate       = max(0, (float) ($this->tax_rate ?? 0));

        $subtotalBruto       = $quantity * $unitPrice;
        $descuentoAplicado   = $subtotalBruto * ($discountRate / 100);
        $subtotalSinImpuesto = max(0, $subtotalBruto - $descuentoAplicado);
        $impuesto            = $subtotalSinImpuesto * ($taxRate / 100);

        $this->tax_amount = round($impuesto, 2);
        $this->subtotal   = round($subtotalSinImpuesto, 2);
        $this->active     = $this->active ?? true;
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