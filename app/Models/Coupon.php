<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'type',
        'min_subtotal',
        'max_discount',
        'label',
        'expired_at',
        'is_active',
    ];

    protected $casts = [
        'discount' => 'float',
        'min_subtotal' => 'float',
        'max_discount' => 'float',
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
