<?php

use App\Http\Controllers\Clients\AccountController;
use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\CartController;
use App\Http\Controllers\Clients\CatalogController;
use App\Http\Controllers\Clients\CheckoutController;
use App\Http\Controllers\Clients\EngagementController;
use App\Http\Controllers\Clients\MomoSimController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{slug}', [CatalogController::class, 'show']);
Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
Route::post('/cart/items', [CartController::class, 'add'])->name('api.cart.add');
Route::patch('/cart/items/{productId}', [CartController::class, 'setQuantity'])->name('api.cart.update');
Route::delete('/cart/items/{productId}', [CartController::class, 'remove'])->name('api.cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('api.cart.clear');
Route::get('/coupon', [CartController::class, 'checkCoupon'])->name('api.cart.coupon');
Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('api.checkout');
Route::get('/orders', [CheckoutController::class, 'orders'])->name('api.orders');
Route::get('/orders/{orderId}', [CheckoutController::class, 'show'])->name('api.orders.show');
Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('api.calculate-shipping');
Route::post('/payments/momo-sim/create', [MomoSimController::class, 'create'])->name('api.momo.create');
Route::get('/payments/momo-sim/{paymentId}', [MomoSimController::class, 'show'])->name('api.momo.show');
Route::post('/payments/momo-sim/{paymentId}/complete', [MomoSimController::class, 'complete'])->name('api.momo.complete');
Route::post('/payments/momo-sim/{paymentId}/fail', [MomoSimController::class, 'fail'])->name('api.momo.fail');

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



Route::prefix('engagement')->group(function () {
    Route::get('/summary', [EngagementController::class, 'summary']);
    Route::get('/reviews', [EngagementController::class, 'reviews']);
    Route::get('/wishlist', [EngagementController::class, 'wishlist']);
    Route::get('/blog-posts/{id}', [EngagementController::class, 'blogPost'])->whereNumber('id');
    Route::post('/reviews', [EngagementController::class, 'storeReview']);
    Route::post('/wishlist', [EngagementController::class, 'storeWishlist']);
    Route::post('/contact', [EngagementController::class, 'storeContact']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

