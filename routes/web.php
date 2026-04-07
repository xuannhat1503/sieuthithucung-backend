<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Clients\AccountController;
use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\ForgotPasswordController;
use App\Http\Controllers\Clients\ResetPasswordController;
use App\Http\Controllers\Clients\ContactController;

 Route::middleware('guest')->group(function () {

        // --- Đăng ký tài khoản ---
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('post-register');
        // --- Đăng nhập ---
        Route::get('/login', [AuthController::class, 'showloginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('post-login');

        // --- Quên mật khẩu---
        Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetlink'])->name('password.email');

        // --- Trang reset mật khẩu ---
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
    });
    
    //  KÍCH HOẠT TÀI KHOẢN QUA EMAIL
    Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');

    Route::middleware(['auth.custom'])->group(function () {
        //  ĐĂNG XUẤT
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::prefix('account')->group(function () {

            // Trang tài khoản
            Route::get('/', [AccountController::class, 'index'])->name('account');

            // Cập nhật thông tin tài khoản
            Route::put('/update', [AccountController::class, 'update'])->name('account.update');

            // Đổi mật khẩu
            Route::post('/change-password', [AccountController::class, 'changePassword'])->name('account.change-password');

            // Thêm địa chỉ giao hàng
            Route::post('/addresses', [AccountController::class, 'addAddress'])->name('account.addresses.add');

            // Cập nhật địa chỉ giao hàng
            Route::put('/addresses/{id}', [AccountController::class, 'updatePrimaryAddress'])->name('account.addresses.update');

            // Xóa địa chỉ
            Route::delete('/addresses/{id}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
        });
    });