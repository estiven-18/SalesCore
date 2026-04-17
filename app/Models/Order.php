<?php
//no se si sirve este modelo, pero lo dejo por si acaso, no se si es necesario para la relacion entre sale y product
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
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

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}