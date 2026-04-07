<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if (str_starts_with($normalizedPath, 'uploads/')) {
            return asset(ltrim($normalizedPath, '/'));
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return asset(ltrim($normalizedPath, '/'));
        }

        return asset('storage/' . ltrim($normalizedPath, '/'));
    }

    protected function wampRootDirectory(): ?string
    {
        $directory = base_path();

        for ($depth = 0; $depth < 8; $depth++) {
            $directory = dirname($directory);

            if ($directory === '' || $directory === DIRECTORY_SEPARATOR) {
                break;
            }

            if (strtolower(basename($directory)) === 'www') {
                return $directory;
            }
        }

        return null;
    }

    protected function frontendAvatarDirectories(): array
    {
        $directories = [];
        $seen = [];

        $addDirectory = static function (?string $directory) use (&$directories, &$seen): void {
            $normalized = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $directory), DIRECTORY_SEPARATOR);

            if ($normalized === '' || isset($seen[$normalized])) {
                return;
            }

            $seen[$normalized] = true;
            $directories[] = $normalized;
        };

        $configuredDirectory = trim((string) env('FRONTEND_AVATAR_DIRECTORY', ''));
        $addDirectory($configuredDirectory);

        $wwwRoot = $this->wampRootDirectory();
        if ($wwwRoot) {
            $addDirectory($wwwRoot . DIRECTORY_SEPARATOR . 'ShopThuCung' . DIRECTORY_SEPARATOR . 'ShopThuCung'
                . DIRECTORY_SEPARATOR . 'sieuthithucung' . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
                . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images'
                . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'users');

            $addDirectory($wwwRoot . DIRECTORY_SEPARATOR . 'sieuthithucung' . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
                . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images'
                . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'users');
        }

        $addDirectory(dirname(base_path()) . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
            . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'users');

        return $directories;
    }

    protected function frontendAvatarDirectory(): string
    {
        return $this->frontendAvatarDirectories()[0];
    }

    protected function deleteStoredAvatar(?string $avatarPath): void
    {
        $normalizedPath = str_replace('\\', '/', (string) $avatarPath);

        if ($normalizedPath === '') {
            return;
        }

        if (str_starts_with($normalizedPath, 'uploads/users/')) {
            $absolutePath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath));

            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }

            return;
        }

        if (
            str_starts_with($normalizedPath, 'assets/images/uploads/users/')
            || str_starts_with($normalizedPath, 'assets/images/uploads/user/')
            || str_starts_with($normalizedPath, 'assets/img/uploads/users/')
        ) {
            $filename = basename($normalizedPath);

            foreach ($this->frontendAvatarDirectories() as $directory) {
                $absolutePath = $directory . DIRECTORY_SEPARATOR . $filename;

                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
        }
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
        $primaryAvatarPath = $directory . DIRECTORY_SEPARATOR . $filename;

        foreach ($this->frontendAvatarDirectories() as $mirrorDirectory) {
            if ($mirrorDirectory === $directory) {
                continue;
            }

            if (!is_dir($mirrorDirectory) && !@mkdir($mirrorDirectory, 0755, true) && !is_dir($mirrorDirectory)) {
                continue;
            }

            @copy($primaryAvatarPath, $mirrorDirectory . DIRECTORY_SEPARATOR . $filename);
        }

        $this->deleteStoredAvatar($user->avatar);

        $relativeAvatarPath = 'assets/images/uploads/users/' . $filename;
        $user->avatar = $relativeAvatarPath;
        $user->save();

        return $relativeAvatarPath;
    }

    protected function formatShippingAddressModel(ShippingAddress $address): array
    {
        $addressLine = trim((string) ($address->address_line ?: $address->address));
        $provinceName = trim((string) ($address->province_name ?: $address->city));
        $districtName = trim((string) $address->district_name);
        $wardName = trim((string) $address->ward_name);
        $fullAddress = collect([$addressLine, $wardName, $districtName, $provinceName])
            ->filter(fn (?string $value) => trim((string) $value) !== '')
            ->implode(', ');

        return [
            'id' => (int) $address->id,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'address' => $address->address,
            'address_line' => $addressLine,
            'city' => $address->city,
            'province_name' => $provinceName,
            'province_code' => (string) $address->province_code,
            'district_name' => $districtName,
            'district_code' => (string) $address->district_code,
            'ward_name' => $wardName,
            'ward_code' => (string) $address->ward_code,
            'is_order_snapshot' => (bool) $address->is_order_snapshot,
            'default' => (bool) $address->default,
            'full_address' => $fullAddress !== '' ? $fullAddress : $address->address,
            'display_label' => trim($address->full_name . ' - ' . ($addressLine !== '' ? $addressLine : ($provinceName ?: $address->address))),
        ];
    }

    protected function createOrderSnapshotAddress(User $user, ShippingAddress $address): ShippingAddress
    {
        $payload = $this->formatShippingAddressModel($address);

        return ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'address' => $payload['full_address'],
            'address_line' => $payload['address_line'],
            'city' => $payload['province_name'],
            'province_name' => $payload['province_name'],
            'province_code' => $payload['province_code'],
            'district_name' => $payload['district_name'],
            'district_code' => $payload['district_code'],
            'ward_name' => $payload['ward_name'],
            'ward_code' => $payload['ward_code'],
            'is_order_snapshot' => true,
            'default' => false,
        ]);
    }

    protected function formatStructuredAddressPayload(array $validated): array
    {
        $addressLine = trim((string) ($validated['address_line'] ?? $validated['address'] ?? ''));
        $provinceName = trim((string) ($validated['province_name'] ?? $validated['city'] ?? ''));
        $districtName = trim((string) ($validated['district_name'] ?? ''));
        $wardName = trim((string) ($validated['ward_name'] ?? ''));

        $fullAddress = collect([$addressLine, $wardName, $districtName, $provinceName])
            ->filter(fn (?string $value) => trim((string) $value) !== '')
            ->implode(', ');

        return [
            'address' => $fullAddress !== '' ? $fullAddress : $addressLine,
            'address_line' => $addressLine,
            'city' => $provinceName,
            'province_name' => $provinceName,
            'province_code' => trim((string) ($validated['province_code'] ?? '')),
            'district_name' => $districtName,
            'district_code' => trim((string) ($validated['district_code'] ?? '')),
            'ward_name' => $wardName,
            'ward_code' => trim((string) ($validated['ward_code'] ?? '')),
        ];
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
            ->where('is_order_snapshot', false)
            ->orderByDesc('default')
            ->orderBy('id')
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
            'addresses' => $addresses->map(fn (ShippingAddress $address) => $this->formatShippingAddressModel($address))->values(),
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
            'address_line' => 'required|string|max:255',
            'province_name' => 'required|string|max:100',
            'province_code' => 'required|string|max:20',
            'district_name' => 'required|string|max:100',
            'district_code' => 'required|string|max:20',
            'ward_name' => 'required|string|max:100',
            'ward_code' => 'required|string|max:20',
            'default' => 'nullable|boolean',
        ], [
            'phone.required' => 'So dien thoai la bat buoc.',
            'phone.regex' => 'So dien thoai phai gom 10 chu so va bat dau bang so 0.',
            'address_line.required' => 'Vui long nhap so nha, ten duong.',
            'province_name.required' => 'Vui long chon tinh/thanh pho.',
            'district_name.required' => 'Vui long chon quan/huyen.',
            'ward_name.required' => 'Vui long chon phuong/xa.',
        ]);

        $user = $this->resolveCustomerByEmail($validated['email']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay tai khoan khach hang.',
            ], 404);
        }

        $isDefault = (bool) ($validated['default'] ?? false);
        if ($isDefault || !ShippingAddress::where('user_id', $user->id)->where('is_order_snapshot', false)->exists()) {
            ShippingAddress::where('user_id', $user->id)->where('is_order_snapshot', false)->update(['default' => 0]);
            $isDefault = true;
        }

        $addressPayload = $this->formatStructuredAddressPayload($validated);

        ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address' => $addressPayload['address'],
            'address_line' => $addressPayload['address_line'],
            'city' => $addressPayload['city'],
            'province_name' => $addressPayload['province_name'],
            'province_code' => $addressPayload['province_code'],
            'district_name' => $addressPayload['district_name'],
            'district_code' => $addressPayload['district_code'],
            'ward_name' => $addressPayload['ward_name'],
            'ward_code' => $addressPayload['ward_code'],
            'is_order_snapshot' => false,
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

        $address = ShippingAddress::where('id', $id)->where('user_id', $user->id)->where('is_order_snapshot', false)->first();
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay dia chi can cap nhat.',
            ], 404);
        }

        ShippingAddress::where('user_id', $user->id)->where('is_order_snapshot', false)->update(['default' => 0]);
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

        $address = ShippingAddress::where('id', $id)->where('user_id', $user->id)->where('is_order_snapshot', false)->first();
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay dia chi can xoa.',
            ], 404);
        }

        $wasDefault = (bool) $address->default;
        try {
            DB::transaction(function () use ($address, $user, $wasDefault) {
                $referencedOrders = Order::where('shipping_address_id', $address->id)->pluck('id');

                if ($referencedOrders->isNotEmpty()) {
                    $snapshotAddress = $this->createOrderSnapshotAddress($user, $address);

                    Order::whereIn('id', $referencedOrders)->update([
                        'shipping_address_id' => $snapshotAddress->id,
                    ]);
                }

                $address->delete();

                if ($wasDefault) {
                    $nextAddress = ShippingAddress::where('user_id', $user->id)
                        ->where('is_order_snapshot', false)
                        ->orderBy('id')
                        ->first();

                    if ($nextAddress) {
                        $nextAddress->update(['default' => 1]);
                    }
                }
            });
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Khong the xoa dia chi nay luc nay. Vui long thu lai sau.',
            ], 422);
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
        $addresses = ShippingAddress::where('user_id', Auth::id())
            ->where('is_order_snapshot', false)
            ->get();
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
            ShippingAddress::where('user_id', Auth::id())->where('is_order_snapshot', false)->update(['default' => 0]);
        }

        ShippingAddress::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'is_order_snapshot' => false,
            'default' => $request->has('default') ? 1 : 0,
        ]);

        return back()->with('success', 'Dia chi da duoc them');
    }

    public function updatePrimaryAddress($id)
    {
        $addresses = ShippingAddress::where('id', $id)->where('user_id', Auth::id())->where('is_order_snapshot', false)->firstOrFail();
        ShippingAddress::where('user_id', Auth::id())->where('is_order_snapshot', false)->update(['default' => 0]);
        $addresses->update(['default' => 1]);
        return back();
    }

    public function deleteAddress($id)
    {
        ShippingAddress::where('id', $id)->where('user_id', Auth::id())->where('is_order_snapshot', false)->delete();
        return back();
    }
}
