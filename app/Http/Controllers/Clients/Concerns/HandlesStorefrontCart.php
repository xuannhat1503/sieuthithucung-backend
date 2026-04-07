<?php

namespace App\Http\Controllers\Clients\Concerns;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait HandlesStorefrontCart
{
    protected function resolveCustomerByEmail(?string $email): ?User
    {
        $normalizedEmail = Str::lower(trim((string) $email));

        if ($normalizedEmail === '') {
            return null;
        }

        $user = User::query()
            ->with('role')
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (!$user || Str::lower((string) optional($user->role)->name) !== 'customer') {
            return null;
        }

        return $user;
    }

    protected function frontendBaseUrl(): string
    {
        return rtrim((string) env('FRONTEND_URL', 'http://localhost/sieuthithucung/sieuthithucung-frontend'), '/');
    }

    protected function frontendPageUrl(string $page, array $query = []): string
    {
        $url = $this->frontendBaseUrl() . '/pages/' . ltrim($page, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    protected function normalizeImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalizedPath = trim($path);

        if (Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return $normalizedPath;
        }

        if (Str::startsWith($normalizedPath, ['assets/', '/assets/'])) {
            return $this->frontendBaseUrl() . '/' . ltrim($normalizedPath, '/');
        }

        if (Str::startsWith($normalizedPath, ['/storage/', '/images/', '/uploads/'])) {
            return URL::to($normalizedPath);
        }

        if (Str::startsWith($normalizedPath, ['storage/', 'images/', 'uploads/'])) {
            return URL::to('/' . ltrim($normalizedPath, '/'));
        }

        return URL::to('/storage/' . ltrim($normalizedPath, '/'));
    }

    protected function loadCartItems(User $user): Collection
    {
        return CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product.category:id,name,slug', 'product.images:id,product_id,image'])
            ->orderByDesc('id')
            ->get();
    }

    protected function mapCartItem(CartItem $item): ?array
    {
        $product = $item->product;
        if (!$product instanceof Product) {
            return null;
        }

        return [
            'id' => (int) $item->id,
            'product_id' => (int) $product->id,
            'quantity' => (int) $item->quantity,
            'product' => [
                'id' => (int) $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'status' => $product->status,
                'unit' => $product->unit,
                'primary_image' => $this->normalizeImagePath(optional($product->images->first())->image),
                'category' => $product->category ? [
                    'id' => (int) $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
            ],
        ];
    }

    protected function buildCartPayload(?User $user): array
    {
        if (!$user) {
            return [
                'cart' => [],
                'total_price' => 0,
                'item_kinds' => 0,
                'item_quantity' => 0,
            ];
        }

        $items = $this->loadCartItems($user);
        $mappedItems = $items
            ->map(fn (CartItem $item) => $this->mapCartItem($item))
            ->filter()
            ->values();

        $subtotal = $mappedItems->sum(function (array $item) {
            return (float) data_get($item, 'product.price', 0) * (int) data_get($item, 'quantity', 0);
        });

        return [
            'cart' => $mappedItems,
            'total_price' => (float) $subtotal,
            'item_kinds' => $mappedItems->count(),
            'item_quantity' => (int) $mappedItems->sum('quantity'),
        ];
    }

    protected function couponDefinitions(): array
    {
        $databaseCoupons = Coupon::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function (Coupon $coupon) {
                return [Str::upper($coupon->code) => [
                    'code' => Str::upper($coupon->code),
                    'type' => $coupon->type,
                    'value' => (float) $coupon->discount,
                    'minSubtotal' => (float) ($coupon->min_subtotal ?? 0),
                    'maxDiscount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
                    'label' => $coupon->label ?: Str::upper($coupon->code),
                    'expired_at' => $coupon->expired_at,
                ]];
            })
            ->all();

        if (!empty($databaseCoupons)) {
            return $databaseCoupons;
        }

        return [
            'PET10' => [
                'code' => 'PET10',
                'type' => 'percent',
                'value' => 10,
                'minSubtotal' => 200000,
                'maxDiscount' => 50000,
                'label' => 'Giam 10% toi da 50.000d cho don tu 200.000d',
            ],
            'SAVE30K' => [
                'code' => 'SAVE30K',
                'type' => 'fixed',
                'value' => 30000,
                'minSubtotal' => 300000,
                'label' => 'Giam truc tiep 30.000d cho don tu 300.000d',
            ],
            'FREESHIP' => [
                'code' => 'FREESHIP',
                'type' => 'shipping',
                'value' => 30000,
                'minSubtotal' => 150000,
                'label' => 'Mien phi van chuyen toi da 30.000d',
            ],
            'THUANNGU' => [
                'code' => 'THUANNGU',
                'type' => 'percent',
                'value' => 100,
                'minSubtotal' => 0,
                'label' => 'Noi hay lam giam cho 100% nhe',
            ],
        ];
    }

    protected function shippingFee(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0;
        }

        return $subtotal >= 500000 ? 0 : 30000;
    }

    protected function taxAmount(float $subtotalAfterDiscount): float
    {
        return 0;
    }

    protected function resolveCoupon(?string $code, float $subtotal, ?float $shippingFeeOverride = null): array
    {
        $normalized = Str::upper(trim((string) $code));
        $coupon = $this->couponDefinitions()[$normalized] ?? null;
        $shippingFee = $shippingFeeOverride ?? $this->shippingFee($subtotal);

        if ($normalized === '') {
            return [
                'valid' => false,
                'code' => '',
                'discount' => 0,
                'message' => 'Chua ap dung ma giam gia.',
                'coupon' => null,
            ];
        }

        if (!$coupon) {
            return [
                'valid' => false,
                'code' => $normalized,
                'discount' => 0,
                'message' => 'Ma giam gia khong hop le.',
                'coupon' => null,
            ];
        }

        if (!empty($coupon['expired_at']) && now()->greaterThan($coupon['expired_at'])) {
            return [
                'valid' => false,
                'code' => $normalized,
                'discount' => 0,
                'message' => 'Ma giam gia da het han.',
                'coupon' => $coupon,
            ];
        }

        if ($subtotal < (float) $coupon['minSubtotal']) {
            return [
                'valid' => false,
                'code' => $normalized,
                'discount' => 0,
                'message' => 'Don hang chua dat gia tri toi thieu de ap dung ma.',
                'coupon' => $coupon,
            ];
        }

        $discount = 0;

        if ($coupon['type'] === 'percent') {
            $discount = round($subtotal * ((float) $coupon['value'] / 100));
            if (!empty($coupon['maxDiscount'])) {
                $discount = min($discount, (float) $coupon['maxDiscount']);
            }
        } elseif ($coupon['type'] === 'fixed') {
            $discount = (float) $coupon['value'];
        } elseif ($coupon['type'] === 'shipping') {
            $discount = min((float) $coupon['value'], $shippingFee);
        }

        return [
            'valid' => true,
            'code' => $normalized,
            'discount' => (float) $discount,
            'message' => $coupon['label'],
            'coupon' => $coupon,
        ];
    }

    protected function shippingRegionGroups(): array
    {
        return [
            'north' => [1, 2, 4, 6, 8, 10, 11, 12, 14, 15, 17, 19, 20, 22, 24, 25, 26, 27, 30, 31, 33, 34, 35, 36, 37],
            'central' => [38, 40, 42, 44, 45, 46, 48, 49, 51, 52, 54, 56, 58, 60, 62, 64, 66],
            'south' => [68, 70, 72, 74, 75, 77, 79, 80, 82, 83, 84, 86, 87, 89, 91, 92, 93, 94, 95, 96],
        ];
    }

    protected function estimateShippingFee(?string $provinceCode = null, ?string $provinceName = null): float
    {
        $code = (int) preg_replace('/\D+/', '', (string) $provinceCode);
        $normalizedProvinceName = Str::lower(trim((string) $provinceName));

        if ($code === 79 || Str::contains($normalizedProvinceName, ['hồ chí minh', 'ho chi minh', 'tp hcm', 'hcm'])) {
            return 15000;
        }

        if ($code === 1 || Str::contains($normalizedProvinceName, ['hà nội', 'ha noi'])) {
            return 35000;
        }

        foreach ($this->shippingRegionGroups() as $region => $provinceCodes) {
            if (!in_array($code, $provinceCodes, true)) {
                continue;
            }

            return match ($region) {
                'south' => 25000,
                'central' => 35000,
                'north' => 40000,
                default => 30000,
            };
        }

        return 30000;
    }

    protected function formatShippingAddressPayload(array $payload): array
    {
        $addressLine = trim((string) ($payload['address_line'] ?? $payload['address'] ?? ''));
        $provinceName = trim((string) ($payload['province_name'] ?? $payload['city'] ?? ''));
        $provinceCode = trim((string) ($payload['province_code'] ?? ''));
        $districtName = trim((string) ($payload['district_name'] ?? ''));
        $districtCode = trim((string) ($payload['district_code'] ?? ''));
        $wardName = trim((string) ($payload['ward_name'] ?? ''));
        $wardCode = trim((string) ($payload['ward_code'] ?? ''));

        $fullAddress = collect([$addressLine, $wardName, $districtName, $provinceName])
            ->filter(fn (?string $value) => trim((string) $value) !== '')
            ->implode(', ');

        return [
            'address' => $fullAddress !== '' ? $fullAddress : $addressLine,
            'address_line' => $addressLine,
            'city' => $provinceName,
            'province_name' => $provinceName,
            'province_code' => $provinceCode,
            'district_name' => $districtName,
            'district_code' => $districtCode,
            'ward_name' => $wardName,
            'ward_code' => $wardCode,
        ];
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

    protected function calculateShippingQuoteForAddress(ShippingAddress $address): array
    {
        $formattedAddress = $this->formatShippingAddressModel($address);
        $shippingFee = $this->estimateShippingFee($formattedAddress['province_code'], $formattedAddress['province_name']);

        return [
            'shipping_fee' => (float) $shippingFee,
            'provider' => 'PetSaigon Delivery',
            'is_estimated' => true,
            'address' => Arr::except($formattedAddress, ['display_label']),
        ];
    }

    protected function resolveCustomerShippingAddress(User $user, int $shippingAddressId): ?ShippingAddress
    {
        if ($shippingAddressId <= 0) {
            return null;
        }

        return ShippingAddress::query()
            ->where('user_id', $user->id)
            ->where('is_order_snapshot', false)
            ->where('id', $shippingAddressId)
            ->first();
    }

    protected function cloneShippingAddressForOrder(User $user, ShippingAddress $sourceAddress): ShippingAddress
    {
        $payload = $this->formatShippingAddressModel($sourceAddress);

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

    protected function createShippingAddress(User $user, array $payload): ShippingAddress
    {
        $formattedPayload = $this->formatShippingAddressPayload($payload);

        return ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => $payload['name'],
            'phone' => $payload['phone'],
            'address' => $formattedPayload['address'],
            'address_line' => $formattedPayload['address_line'],
            'city' => $formattedPayload['province_name'],
            'province_name' => $formattedPayload['province_name'],
            'province_code' => $formattedPayload['province_code'],
            'district_name' => $formattedPayload['district_name'],
            'district_code' => $formattedPayload['district_code'],
            'ward_name' => $formattedPayload['ward_name'],
            'ward_code' => $formattedPayload['ward_code'],
            'is_order_snapshot' => false,
            'default' => false,
        ]);
    }
}
