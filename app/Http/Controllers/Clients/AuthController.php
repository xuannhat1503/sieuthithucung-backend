<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\ActivationMail;
use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Flasher\Toastr\Prime\toastr;

class AuthController extends Controller
{
    protected function buildMailFailurePayload(string $message, \Throwable $exception): array
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'hint' => 'Kiem tra MAIL_* tren Railway (MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_FROM_ADDRESS).',
        ];

        if ((bool) config('app.debug')) {
            $payload['debug_error'] = $exception->getMessage();
        }

        return $payload;
    }

    protected function resolveCustomerRoleId(): int
    {
        $existingRole = Role::query()
            ->whereRaw('LOWER(name) = ?', ['customer'])
            ->first();

        if ($existingRole) {
            return (int) $existingRole->id;
        }

        return (int) Role::create(['name' => 'customer'])->id;
    }

    protected function hasCustomerRole(?Role $role): bool
    {
        return Str::lower((string) optional($role)->name) === 'customer';
    }

    protected function frontendHomeUrl(array $query = []): string
    {
        $baseUrl = rtrim(env('FRONTEND_URL', 'http://localhost/sieuthithucung/sieuthithucung-frontend'), '/');
        $homeUrl = $baseUrl . '/pages/home.html';

        if (!empty($query)) {
            $homeUrl .= '?' . http_build_query($query);
        }

        return $homeUrl;
    }

    protected function frontendLoginUrl(array $query = []): string
    {
        $baseUrl = rtrim(env('FRONTEND_URL', 'http://localhost/sieuthithucung/sieuthithucung-frontend'), '/');
        $loginUrl = $baseUrl . '/pages/login.html';

        if (!empty($query)) {
            $loginUrl .= '?' . http_build_query($query);
        }

        return $loginUrl;
    }

    protected function buildActivationLink(string $token): string
    {
        return url('/api/auth/activate/' . $token);
    }

    protected function frontendForgotUrl(array $query = []): string
    {
        $baseUrl = rtrim(env('FRONTEND_URL', 'http://localhost/sieuthithucung/sieuthithucung-frontend'), '/');
        $forgotUrl = $baseUrl . '/pages/forgot-password.html';

        if (!empty($query)) {
            $forgotUrl .= '?' . http_build_query($query);
        }

        return $forgotUrl;
    }

    protected function frontendResetUrl(array $query = []): string
    {
        $baseUrl = rtrim(env('FRONTEND_URL', 'http://localhost/sieuthithucung/sieuthithucung-frontend'), '/');
        $resetUrl = $baseUrl . '/pages/reset-password.html';

        if (!empty($query)) {
            $resetUrl .= '?' . http_build_query($query);
        }

        return $resetUrl;
    }

    protected function issueActivationToken(User $user): User
    {
        $user->activation_token = Str::random(64);
        $user->save();

        return $user;
    }

    protected function sendActivationEmail(User $user): void
    {
        Mail::to($user->email)->send(
            new ActivationMail($user->activation_token, $user, $this->buildActivationLink($user->activation_token))
        );

        Log::info('Activation email sent successfully.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $user->status,
        ]);
    }

    protected function issuePasswordResetToken(User $user): string
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => hash('sha256', $plainToken),
                'created_at' => now(),
            ]
        );

        return $plainToken;
    }

    protected function sendPasswordResetEmail(User $user, string $plainToken): void
    {
        $resetUrl = url('/api/auth/reset-password/' . $plainToken . '?email=' . urlencode($user->email));

        Mail::to($user->email)->send(
            new ResetPasswordMail($plainToken, $user, $resetUrl)
        );

        Log::info('Password reset email sent successfully.', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    protected function resolvePasswordResetRecord(string $email, string $token): ?object
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', hash('sha256', $token))
            ->first();
    }

    public function showRegisterForm()
    {
        return view('clients.pages.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'Ten la bat buoc',
            'email.required' => 'Email la bat buoc',
            'email.unique' => 'Email nay da duoc su dung',
            'password.required' => 'Mat khau la bat buoc',
            'password.min' => 'Mat khau phai co it nhat 6 ky tu',
        ]);

        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            if ($existingUser->isPending()) {
                toastr()->error('Email da duoc dang ky va dang cho kich hoat');
                return redirect()->route('register');
            }

            return redirect()->route('register');
        }

        $token = Str::random(64);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending',
            'role_id' => $this->resolveCustomerRoleId(),
            'activation_token' => $token,
        ]);

        Mail::to($user->email)->send(new ActivationMail($token, $user, $this->buildActivationLink($token)));

        toastr()->success('Dang ky tai khoan thanh cong, vui long kiem tra email de kich hoat tai khoan.');
        return redirect()->route('login');
    }

    public function apiRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Ho va ten la bat buoc.',
            'name.min' => 'Ho va ten phai co it nhat 3 ky tu.',
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
            'password.required' => 'Mat khau la bat buoc.',
            'password.min' => 'Mat khau phai co it nhat 6 ky tu.',
            'password.confirmed' => 'Mat khau nhap lai khong khop.',
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            if ($existingUser->isPending()) {
                $this->issueActivationToken($existingUser);

                try {
                    $this->sendActivationEmail($existingUser);
                } catch (\Throwable $exception) {
                    Log::error('Resend activation email failed.', [
                        'user_id' => $existingUser->id,
                        'email' => $existingUser->email,
                        'error' => $exception->getMessage(),
                        'mailer' => env('MAIL_MAILER'),
                        'mail_host' => env('MAIL_HOST'),
                        'mail_port' => env('MAIL_PORT'),
                        'mail_from_address' => env('MAIL_FROM_ADDRESS'),
                    ]);

                    return response()->json(
                        $this->buildMailFailurePayload(
                            'Tai khoan nay chua kich hoat, nhung gui lai email kich hoat that bai. Vui long thu lai.',
                            $exception
                        ),
                        500
                    );
                }

                return response()->json([
                    'success' => true,
                    'status' => 'activation_resent',
                    'message' => 'Tai khoan nay chua kich hoat. Chung toi da gui lai email kich hoat cho ban.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Email nay da duoc su dung.',
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'pending',
            'role_id' => $this->resolveCustomerRoleId(),
            'activation_token' => Str::random(64),
        ]);

        try {
            $this->sendActivationEmail($user);
        } catch (\Throwable $exception) {
            Log::error('Activation email send failed during register.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
                'mailer' => env('MAIL_MAILER'),
                'mail_host' => env('MAIL_HOST'),
                'mail_port' => env('MAIL_PORT'),
                'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            ]);

            $user->delete();

            return response()->json(
                $this->buildMailFailurePayload(
                    'Dang ky chua hoan tat vi khong gui duoc email kich hoat. Vui long thu lai.',
                    $exception
                ),
                500
            );
        }

        return response()->json([
            'success' => true,
            'status' => 'activation_sent',
            'message' => 'Da gui kich hoat ve email cua ban. Vui long kiem tra email de kich hoat tai khoan.',
        ], 201);
    }

    public function apiLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
            'password.required' => 'Mat khau la bat buoc.',
            'password.min' => 'Mat khau phai co it nhat 6 ky tu.',
        ]);

        $user = User::with('role')->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Thong tin dang nhap khong chinh xac.',
            ], 422);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan chua kich hoat. Vui long kiem tra email de kich hoat tai khoan.',
            ], 403);
        }

        if (!$this->hasCustomerRole($user->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Chi tai khoan khach hang moi co the dang nhap tai day.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dang nhap thanh cong.',
            'redirect_url' => $this->frontendHomeUrl([
                'login' => 'success',
            ]),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => Str::lower((string) $user->role->name),
                'status' => $user->status,
            ],
        ]);
    }

    public function apiForgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Neu email ton tai trong he thong, chung toi da gui lien ket dat lai mat khau.',
            ]);
        }

        try {
            $plainToken = $this->issuePasswordResetToken($user);
            $this->sendPasswordResetEmail($user, $plainToken);
        } catch (\Throwable $exception) {
            Log::error('Password reset email send failed.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Khong gui duoc email dat lai mat khau. Vui long thu lai.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Da gui lien ket dat lai mat khau ve email cua ban. Vui long kiem tra email.',
        ]);
    }

    public function resetPasswordFromEmail(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');

        if ($email === '') {
            return redirect()->away($this->frontendForgotUrl([
                'reset' => 'invalid',
            ]));
        }

        $resetRecord = $this->resolvePasswordResetRecord($email, $token);

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect()->away($this->frontendForgotUrl([
                'reset' => 'invalid',
            ]));
        }

        return redirect()->away($this->frontendResetUrl([
            'token' => $token,
            'email' => $email,
        ]));
    }

    public function apiResetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
            'token.required' => 'Token dat lai mat khau la bat buoc.',
            'password.required' => 'Mat khau moi la bat buoc.',
            'password.min' => 'Mat khau moi phai co it nhat 6 ky tu.',
            'password.confirmed' => 'Mat khau nhap lai khong khop.',
        ]);

        $user = User::where('email', $validated['email'])->first();
        $resetRecord = $this->resolvePasswordResetRecord($validated['email'], $validated['token']);

        if (!$user || !$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Lien ket dat lai mat khau khong hop le hoac da het han.',
            ], 422);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return response()->json([
                'success' => false,
                'message' => 'Lien ket dat lai mat khau da het han. Vui long gui lai yeu cau moi.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dat lai mat khau thanh cong. Moi ban dang nhap lai.',
            'redirect_url' => $this->frontendLoginUrl([
                'reset' => 'success',
            ]),
        ]);
    }

    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if ($user) {
            $user->status = 'active';
            $user->activation_token = null;
            $user->save();
            toastr()->success('Kich hoat tai khoan thanh cong');
            return redirect()->route('login');
        }

        toastr()->error('Token khong hop le hoac da het han.');
        return redirect()->back();
    }

    public function activateFromEmail(string $token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return redirect()->away($this->frontendLoginUrl([
                'activation' => 'invalid',
            ]));
        }

        if ($user->status !== 'active') {
            $user->status = 'active';
            $user->activation_token = null;
            $user->save();
        }

        return redirect()->away($this->frontendLoginUrl([
            'activation' => 'success',
        ]));
    }

    public function showloginForm()
    {
        return view('clients.pages.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email la bat buoc',
            'email.email' => 'Email khong hop le',
            'password.required' => 'Mat khau la bat buoc',
            'password.min' => 'Mat khau phai co it nhat 6 ky tu',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 'active'])) {
            if ($this->hasCustomerRole(Auth::user()->role)) {
                $request->session()->regenerate();
                toastr()->success('Dang nhap thanh cong');
                return redirect()->route('home');
            }

            Auth::logout();
            toastr()->warning('Ban khong co quyen truy cap tai khoan nay.');
            return redirect()->back();
        }

        toastr()->error('Thong tin dang nhap khong chinh xac hoac tai khoan chua kich hoat.');
        return redirect()->back();
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        toastr()->success('Dang xuat thanh cong');
        return redirect()->route('login');
    }
}
