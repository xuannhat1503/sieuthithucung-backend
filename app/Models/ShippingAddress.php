<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address',
        'address_line',
        'city',
        'province_name',
        'province_code',
        'district_name',
        'district_code',
        'ward_name',
        'ward_code',
        'is_order_snapshot',
        'default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
