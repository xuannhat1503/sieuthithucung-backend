<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('clients.auth.forgot-password');
    }
    public function sendResetlink(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:users,email',
            ],
            [
                'email.required' => 'Email là bắt buộc',
                'email.email' => 'Email không hợp lệ',
                'email.exists' => 'Email không tồn tại trong hệ thống',

            ]
        );
        $status = Password::sendResetLink($request->only('email'));
        if($status === Password::RESET_LINK_SENT)
        {
            toastr()->success('Liên kết đặt lại mật khẩu đã được gửi đến email của bạn');
            return back();
        }
        toastr()->error('Không thể gửi lại email đặt lại mật khẩu');
        return back()->withErrors(['email' => __($status)]);
    }
}
