<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // dòng này thay cho Model
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = true;
  
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone_number',
        'avatar',
        'address',
        'role_id',
        'activation_token',
        'google_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    //check status
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isBanned()
    {
        return $this->status === 'banned';
    }

    public function isDelete()
    {
        return $this->status === 'deleted';
    }

    public function isDeleted()
    {
        return $this->isDelete();
    }

    public function getAvatarUrlAttribute()
    {
        $avatar = str_replace('\\', '/', (string) $this->avatar);
        $frontendBaseUrl = rtrim(env('FRONTEND_URL', 'http://127.0.0.1:5500'), '/');

        if ($avatar === '') {
            return $frontendBaseUrl . '/assets/images/uploads/users/default.png';
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        if (str_starts_with($avatar, 'assets/')) {
            return $frontendBaseUrl . '/' . ltrim($avatar, '/');
        }

        if (str_starts_with($avatar, 'uploads/')) {
            return asset(ltrim($avatar, '/'));
        }

        if (str_starts_with($avatar, 'storage/')) {
            return asset(ltrim($avatar, '/'));
        }

        return asset('storage/' . ltrim($avatar, '/'));
    }
}
