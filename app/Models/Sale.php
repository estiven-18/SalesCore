<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'customer_id',
        'user_id',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (!$sale->user_id) {
                $sale->user_id = Auth::id();
            }

            $sale->subtotal = $sale->subtotal ?? 0;
            $sale->tax_total = $sale->tax_total ?? 0;
            $sale->discount_total = $sale->discount_total ?? 0;
            $sale->total = $sale->total ?? 0;
        });
    }

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function recalculateTotalsFromItems(): void
    {
        $items = $this->items()->get();

        $subtotalWithoutTax = (float) $items->sum('subtotal');
        $taxTotal = (float) $items->sum('tax_amount');
        $discountTotal = (float) $items->sum(function ($item) {
            $baseAmount = (float) $item->quantity * (float) $item->unit_price;
            $discountRate = min(100, max(0, (float) $item->discount));

            return $baseAmount * ($discountRate / 100);
        });
        $totalWithTax = $subtotalWithoutTax + $taxTotal;

        $this->forceFill([
            'subtotal' => round($subtotalWithoutTax, 2),
            'tax_total' => round($taxTotal, 2),
            'discount_total' => round($discountTotal, 2),
            'total' => round($totalWithTax, 2),
        ])->saveQuietly();
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}