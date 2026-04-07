<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Clients\Concerns\HandlesStorefrontCart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    use HandlesStorefrontCart;

    public function checkout(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'shipping_address_id' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $selectedShippingAddress = $this->resolveCustomerShippingAddress($user, (int) $payload['shipping_address_id']);
        if (!$selectedShippingAddress) {
            return response()->json(['message' => 'Khong tim thay dia chi giao hang da chon.'], 404);
        }

        $cartItems = $this->loadCartItems($user);
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Gio hang dang trong.'], 422);
        }

        $subtotal = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product) {
                return response()->json(['message' => 'San pham trong gio hang khong ton tai.'], 404);
            }

            if ((int) $product->stock < (int) $item->quantity) {
                return response()->json(['message' => $product->name . ' khong du ton kho.'], 422);
            }

            $subtotal += (float) $product->price * (int) $item->quantity;
        }

        $shippingQuote = $this->calculateShippingQuoteForAddress($selectedShippingAddress);
        $shippingFee = (float) $shippingQuote['shipping_fee'];
        $couponState = $this->resolveCoupon($payload['coupon_code'] ?? null, $subtotal, $shippingFee);
        if (!empty($payload['coupon_code']) && !$couponState['valid']) {
            return response()->json(['message' => $couponState['message']], 422);
        }

        $discountAmount = (float) $couponState['discount'];
        $taxableSubtotal = max(0, $subtotal - $discountAmount);
        $taxAmount = $this->taxAmount($taxableSubtotal);
        $total = max(0, $taxableSubtotal + $shippingFee + $taxAmount);

        $order = DB::transaction(function () use ($user, $selectedShippingAddress, $cartItems, $total) {
            $shippingAddress = $this->cloneShippingAddressForOrder($user, $selectedShippingAddress);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending',
                'shipping_address_id' => $shippingAddress->id,
            ]);

            foreach ($cartItems as $item) {
                $product = $item->product;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $product->price,
                ]);

                $product->stock = max(0, (int) $product->stock - (int) $item->quantity);
                $product->save();
            }

            CartItem::query()
                ->where('user_id', $user->id)
                ->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Dat hang thanh cong.',
            'order_id' => $order->id,
            'subtotal' => (float) $subtotal,
            'shipping_fee' => (float) $shippingFee,
            'tax' => (float) $taxAmount,
            'discount' => (float) $discountAmount,
            'total_price' => (float) $total,
            'coupon_code' => $couponState['code'] ?? '',
            'payment_method' => $payload['payment_method'] ?? 'cod',
            'shipping_provider' => $shippingQuote['provider'] ?? 'PetSaigon Delivery',
        ]);
    }

    public function calculateShipping(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'shipping_address_id' => ['required', 'integer'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $shippingAddress = $this->resolveCustomerShippingAddress($user, (int) $payload['shipping_address_id']);
        if (!$shippingAddress) {
            return response()->json(['message' => 'Khong tim thay dia chi can tinh phi ship.'], 404);
        }

        return response()->json($this->calculateShippingQuoteForAddress($shippingAddress));
    }

    public function orders(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerByEmail($request->query('email', $request->input('email')));
        if (!$user) {
            return response()->json(['orders' => []]);
        }

        $orders = Order::query()
            ->with(['items.product:id,name,slug', 'shippingAddress:id,full_name,phone,address,city'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, int $orderId): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $order = Order::query()
            ->with(['items.product:id,name,slug', 'shippingAddress:id,full_name,phone,address,city'])
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Khong tim thay don hang.'], 404);
        }

        return response()->json([
            'order' => [
                'id' => (int) $order->id,
                'status' => (string) $order->status,
                'total_price' => (float) $order->total_price,
                'created_at' => optional($order->created_at)?->toIso8601String(),
                'created_at_label' => optional($order->created_at)->format('d/m/Y H:i'),
                'shipping_address' => $order->shippingAddress ? [
                    'full_name' => $order->shippingAddress->full_name,
                    'phone' => $order->shippingAddress->phone,
                    'address' => $order->shippingAddress->address,
                    'city' => $order->shippingAddress->city,
                ] : null,
                'items' => $order->items->map(function (OrderItem $item) {
                    return [
                        'id' => (int) $item->id,
                        'product_id' => (int) $item->product_id,
                        'quantity' => (int) $item->quantity,
                        'item_price' => (float) $item->price,
                        'product_name' => (string) optional($item->product)->name,
                        'product_slug' => (string) optional($item->product)->slug,
                    ];
                })->values(),
            ],
        ]);
    }
}
