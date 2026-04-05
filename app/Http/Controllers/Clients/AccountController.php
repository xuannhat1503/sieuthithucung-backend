<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    protected function resolveCustomerByEmail(string $email): ?User
    {
        $user = User::with('role')->where('email', $email)->first();

        if (!$user || !$user->role || strtolower((string) $user->role->name) !== 'customer') {
            return null;
        }

        return $user;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        $normalized = preg_replace('/\D+/', '', (string) $phone);

        return $normalized !== '' ? $normalized : null;
    }

    protected function frontendBaseUrl(): string
    {
        return rtrim(env('FRONTEND_URL', 'http://127.0.0.1:5500'), '/');
    }

    protected function defaultAvatarPath(): string
    {
        return 'assets/images/uploads/users/default.png';
    }

    protected function resolveAvatarUrl(?string $avatarPath): string
    {
        $normalizedPath = str_replace('\\', '/', (string) $avatarPath);

        if ($normalizedPath === '') {
            return $this->frontendBaseUrl() . '/' . $this->defaultAvatarPath();
        }

        if (filter_var($normalizedPath, FILTER_VALIDATE_URL)) {
            return $normalizedPath;
        }

        if (str_starts_with($normalizedPath, 'assets/')) {
            return $this->frontendBaseUrl() . '/' . ltrim($normalizedPath, '/');
        }

        return asset('storage/' . ltrim($normalizedPath, '/'));
    }

    protected function frontendAvatarDirectory(): string
    {
        return dirname(base_path()) . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'users';
    }

    protected function storeAvatarForUser(User $user, UploadedFile $avatarFile): string
    {
        $directory = $this->frontendAvatarDirectory();

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($avatarFile->getClientOriginalExtension() ?: $avatarFile->extension() ?: 'jpg');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $extension;
        $avatarFile->move($directory, $filename);

        $oldAvatarPath = str_replace('\\', '/', (string) $user->avatar);
        if (
            $oldAvatarPath !== ''
            && (
                str_starts_with($oldAvatarPath, 'assets/images/uploads/users/')
                || str_starts_with($oldAvatarPath, 'assets/images/uploads/user/')
                || str_starts_with($oldAvatarPath, 'assets/img/uploads/users/')
            )
        ) {
            $absoluteOldAvatar = dirname(base_path()) . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
                . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $oldAvatarPath);

            if (is_file($absoluteOldAvatar)) {
                unlink($absoluteOldAvatar);
            }
        }

        $relativeAvatarPath = 'assets/images/uploads/users/' . $filename;
        $user->avatar = $relativeAvatarPath;
        $user->save();

        return $relativeAvatarPath;
    }

    public function apiSummary(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $addresses = ShippingAddress::where('user_id', $user->id)
            ->orderByDesc('default')
            ->orderByDesc('id')
            ->get();

        $orders = Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'address' => $user->address,
                'avatar' => $this->resolveAvatarUrl($user->avatar),
                'status' => $user->status,
            ],
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_price' => (float) $order->total_price,
                    'created_at' => optional($order->created_at)->format('d/m/Y'),
                ];
            })->values(),
            'addresses' => $addresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'full_name' => $address->full_name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city,
                    'default' => (bool) $address->default,
                ];
            })->values(),
        ]);
    }

    public function apiAddAddress(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone($request->input('phone')),
        ]);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'default' => 'nullable|boolean',
        ], [
            'phone.required' => 'So dien thoai la bat buoc.',
            'phone.regex' => 'So dien thoai phai gom 10 chu so va bat dau bang so 0.',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $isDefault = (bool) ($validated['default'] ?? false);
        if ($isDefault || !ShippingAddress::where('user_id', $user->id)->exists()) {
            ShippingAddress::where('user_id', $user->id)->update(['default' => 0]);
            $isDefault = true;
        }

        ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'default' => $isDefault ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Them dia chi moi thanh cong.',
        ]);
    }

    public function apiSetDefaultAddress(Request $request, int $id)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $address = ShippingAddress::where('id', $id)->where('user_id', $user->id)->first();
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay dia chi can cap nhat.',
            ], 404);
        }

        ShippingAddress::where('user_id', $user->id)->update(['default' => 0]);
        $address->update(['default' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Da cap nhat dia chi mac dinh.',
        ]);
    }

    public function apiDeleteAddress(Request $request, int $id)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $address = ShippingAddress::where('id', $id)->where('user_id', $user->id)->first();
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay dia chi can xoa.',
            ], 404);
        }

        $wasDefault = (bool) $address->default;
        $address->delete();

        if ($wasDefault) {
            $nextAddress = ShippingAddress::where('user_id', $user->id)->orderBy('id')->first();
            if ($nextAddress) {
                $nextAddress->update(['default' => 1]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Xoa dia chi thanh cong.',
        ]);
    }

    public function apiChangePassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_new_password' => 'required|same:new_password',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
            'current_password.required' => 'Vui long nhap mat khau hien tai.',
            'new_password.required' => 'Mat khau moi khong duoc de trong.',
            'new_password.min' => 'Mat khau moi phai co it nhat 6 ky tu.',
            'confirm_new_password.required' => 'Vui long nhap lai mat khau moi.',
            'confirm_new_password.same' => 'Mat khau nhap lai khong khop.',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mat khau hien tai khong dung.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doi mat khau thanh cong.',
        ]);
    }

    public function apiUpdateProfile(Request $request)
    {
        $request->merge([
            'phone_number' => $this->normalizePhone($request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|string|min:2|max:255',
            'phone_number' => ['nullable', 'regex:/^0\d{9}$/'],
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong hop le.',
            'name.required' => 'Ho va ten la bat buoc.',
            'name.min' => 'Ho va ten phai co it nhat 2 ky tu.',
            'phone_number.regex' => 'So dien thoai phai gom 10 chu so va bat dau bang so 0.',
            'avatar.image' => 'Tap tin tai len phai la hinh anh.',
            'avatar.mimes' => 'Anh dai dien chi ho tro JPG, PNG, GIF hoac WEBP.',
            'avatar.max' => 'Anh dai dien khong duoc vuot qua 5MB.',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->address = $validated['address'] ?? null;

        if ($request->hasFile('avatar')) {
            $this->storeAvatarForUser($user, $request->file('avatar'));
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat thong tin tai khoan thanh cong.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'address' => $user->address,
                'avatar' => $this->resolveAvatarUrl($user->avatar),
            ],
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        $addresses = ShippingAddress::where('user_id', Auth::id())->get();
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('clients.pages.account', compact('user', 'addresses', 'orders'));
    }

    public function update(Request $request)
    {
        $request->merge([
            'ltn__phone_number' => $this->normalizePhone($request->input('ltn__phone_number')),
        ]);

        $request->validate([
            'ltn__name' => 'required|string|min:2|max:255',
            'ltn__phone_number' => ['nullable', 'regex:/^0\d{9}$/'],
            'ltn__address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'ltn__name.required' => 'Ho va ten la bat buoc.',
            'ltn__name.min' => 'Ho va ten phai co it nhat 2 ky tu.',
            'ltn__phone_number.regex' => 'So dien thoai phai gom 10 chu so va bat dau bang so 0.',
            'avatar.image' => 'Tap tin tai len phai la hinh anh.',
            'avatar.mimes' => 'Anh dai dien chi ho tro JPG, PNG, GIF hoac WEBP.',
            'avatar.max' => 'Anh dai dien khong duoc vuot qua 5MB.',
        ]);

        $user = Auth::user();
        $user->name = $request->input('ltn__name');
        $user->phone_number = $request->input('ltn__phone_number');
        $user->address = $request->input('ltn__address');

        if ($request->hasFile('avatar')) {
            $this->storeAvatarForUser($user, $request->file('avatar'));
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat thong tin thanh cong',
            'avatar' => $this->resolveAvatarUrl($user->avatar),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_new_password' => 'required|same:new_password',
        ], [
            'current_password.required' => 'Vui long nhap mat khau hien tai.',
            'new_password.required' => 'Mat khau moi khong duoc de trong.',
            'new_password.min' => 'Mat khau moi phai co it nhat 6 ky tu.',
            'confirm_new_password.required' => 'Vui long nhap lai mat khau moi.',
            'confirm_new_password.same' => 'Mat khau nhap lai khong khop.',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['Mat khau hien tai khong dung!']]], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Doi mat khau thanh cong',
        ]);
    }

    public function addAddress(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhone($request->input('phone')),
        ]);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
        ], [
            'phone.required' => 'So dien thoai la bat buoc.',
            'phone.regex' => 'So dien thoai phai gom 10 chu so va bat dau bang so 0.',
        ]);

        if ($request->has('default')) {
            ShippingAddress::where('user_id', Auth::id())->update(['default' => 0]);
        }

        ShippingAddress::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'default' => $request->has('default') ? 1 : 0,
        ]);

        return back()->with('success', 'Dia chi da duoc them');
    }

    public function updatePrimaryAddress($id)
    {
        $addresses = ShippingAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        ShippingAddress::where('user_id', Auth::id())->update(['default' => 0]);
        $addresses->update(['default' => 1]);
        return back();
    }

    public function deleteAddress($id)
    {
        ShippingAddress::where('id', $id)->where('user_id', Auth::id())->delete();
        return back();
    }
}
