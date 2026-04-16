<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'number',
        'customer_id',
        'total',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $last = static::latest('id')->first();
            $nextNumber = $last ? ($last->id + 1) : 1;
            $order->number = 'P-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });
    }


    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}