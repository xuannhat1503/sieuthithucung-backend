<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SinhVien extends Model
{
    use HasFactory;
    
    // Chỉ định chính xác tên bảng trong database
    protected $table = 'sinh_vien'; 
}