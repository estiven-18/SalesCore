<?php
//no se si sirve este modelo, pero lo dejo por si acaso, no se si es necesario para la relacion entre sale y product
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SaleItem extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}