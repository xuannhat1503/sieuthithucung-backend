<?php

use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'apiRegister'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.auth.login');
    Route::post('/forgot-password', [AuthController::class, 'apiForgotPassword'])->name('api.auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'apiResetPassword'])->name('api.auth.reset-password');
    Route::get('/activate/{token}', [AuthController::class, 'activateFromEmail'])->name('api.auth.activate');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordFromEmail'])->name('api.auth.reset-password.email');
});

Route::get('/account/summary', [AccountController::class, 'apiSummary'])->name('api.account.summary');
Route::post('/account/profile', [AccountController::class, 'apiUpdateProfile'])->name('api.account.profile');
Route::post('/account/addresses', [AccountController::class, 'apiAddAddress'])->name('api.account.addresses.add');
Route::put('/account/addresses/{id}/default', [AccountController::class, 'apiSetDefaultAddress'])->name('api.account.addresses.default');
Route::delete('/account/addresses/{id}', [AccountController::class, 'apiDeleteAddress'])->name('api.account.addresses.delete');
Route::post('/account/change-password', [AccountController::class, 'apiChangePassword'])->name('api.account.change-password');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
