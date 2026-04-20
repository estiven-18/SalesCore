<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'alertable_type',
        'alertable_id',
        'type',
        'message',
        'status',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function alertable()
    {
        return $this->morphTo();
    }
}