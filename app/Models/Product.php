<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'stock_security',
        'tax_rate',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            \App\Models\InventoryMovement::create([
                'product_id' => $product->id,
                'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                //tipo es : entrada - ajuste - venta - devolucion 
                //type es : initial, sale, return, adjustment
                'type'       => 'initial',
                'quantity'   => $product->stock,
                'reason'     => 'Initial stock on product creation',
                'active'     => true,
            ]);
        });
    }

    /**
     * Crea una alerta si el stock cae por debajo o igual al stock de seguridad.
     */
    public function checkLowStockAlert(): void
    {
        $stockSecurity = (int) $this->stock_security;
        $currentStock = (int) $this->stock;

        if ($stockSecurity > 0 && $currentStock <= $stockSecurity) {

            // Evitar duplicar alertas activas para el mismo producto
            $exists = Alert::where('alertable_type', self::class)
                ->where('alertable_id', $this->id)
                ->where('type', 'low_stock')
                ->where('status', 'active')
                ->exists();

            if (!$exists) {
                Alert::create([
                    'alertable_type' => self::class,
                    'alertable_id' => $this->id,
                    'type' => 'low stock',
                    'message' => "Low Stock: \"{$this->name}\" has {$currentStock} units (minimum: {$stockSecurity}).",
                    'status' => 'Not resolved',
                    'active' => true,
                ]);
            }
        }
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function alerts()
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
}
