<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
    'name',
    'slug',
    'category_id',
    'description',
    'price',
    'stock',
    'status',
    'unit',
];


    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

      public function cartItem()
    {
        return $this->hasMany(CartItem::class);
    }
    
      public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->orderby('id', 'ASC');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class); 
    }
public function getImageUrlAttribute()
{
    // Nếu có ảnh đầu tiên
    if ($this->firstImage && $this->firstImage->image) {
        return asset('storage/' . $this->firstImage->image);
    }

    // Nếu không có ảnh
    return asset('storage/uploads/products/product_default.png');
}


    
}
