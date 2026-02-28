<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SinhVien;

class SinhVienController extends Controller
{
    public function index()
    {
        // Lấy toàn bộ dữ liệu từ bảng sinh_vien
        $danhSach = SinhVien::all();
        
        // Trả về dữ liệu định dạng JSON cho JavaScript đọc
        return response()->json($danhSach);
    }
}
