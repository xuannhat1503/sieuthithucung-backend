<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'payment_method', 'transaction_id', 'amount', 'status', 'paid_at', 'paid_ay'];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
        'paid_ay' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
