<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nếu là đường dẫn admin
        if ($request->is('admin') || $request->is('admin/*')) {
            if (!Auth::guard('admin')->check()) {
                toastr()->error('Vui lòng đăng nhập để vào trang quản trị.');
                return redirect()->route('admin.login');
            }
        } 
        // Nếu là người dùng  (client)
        else {
            if (!Auth::guard('web')->check()) {
                toastr()->error('Vui lòng đăng nhập để thực hiện chức năng này.');
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
