<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Clients\Concerns\HandlesStorefrontCart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use HandlesStorefrontCart;

    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerByEmail($request->query('email', $request->input('email')));

        return response()->json($this->buildCartPayload($user));
    }

    public function add(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $product = Product::query()->find($payload['product_id']);
        if (!$product) {
            return response()->json(['message' => 'Khong tim thay san pham.'], 404);
        }

        $requestedQuantity = (int) ($payload['quantity'] ?? 1);
        $cartItem = CartItem::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $nextQuantity = (int) ($cartItem->quantity ?? 0) + $requestedQuantity;
        if ((int) $product->stock <= 0 || $nextQuantity > (int) $product->stock) {
            return response()->json(['message' => 'San pham hien khong du ton kho de them vao gio hang.'], 422);
        }

        $cartItem->quantity = $nextQuantity;
        $cartItem->save();

        return response()->json($this->buildCartPayload($user));
    }

    public function setQuantity(Request $request, int $productId): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $product = Product::query()->find($productId);
        if (!$product) {
            return response()->json(['message' => 'Khong tim thay san pham.'], 404);
        }

        $quantity = (int) $payload['quantity'];
        if ($quantity > (int) $product->stock) {
            return response()->json(['message' => 'So luong vuot qua ton kho hien co.'], 422);
        }

        $cartItem = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Khong tim thay san pham trong gio hang.'], 404);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return response()->json($this->buildCartPayload($user));
    }

    public function remove(Request $request, int $productId): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        $cartItem = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Khong tim thay san pham trong gio hang.'], 404);
        }

        $cartItem->delete();

        return response()->json($this->buildCartPayload($user));
    }

    public function clear(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $this->resolveCustomerByEmail($payload['email']);
        if (!$user) {
            return response()->json(['message' => 'Khong tim thay tai khoan khach hang.'], 404);
        }

        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();

        return response()->json($this->buildCartPayload($user));
    }

    public function checkCoupon(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);

        $result = $this->resolveCoupon($payload['code'] ?? null, (float) ($payload['subtotal'] ?? 0));

        return response()->json([
            'success' => $result['valid'],
            'code' => $result['code'],
            'discount' => $result['discount'],
            'message' => $result['message'],
            'coupon' => $result['coupon'],
        ]);
    }
}
