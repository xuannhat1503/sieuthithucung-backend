<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Clients\Concerns\HandlesStorefrontCart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MomoSimController extends Controller
{
    use HandlesStorefrontCart;

    public function create(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'shipping_address_id' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
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

        [$order, $payment] = DB::transaction(function () use ($user, $selectedShippingAddress, $cartItems, $total) {
            $shippingAddress = $this->cloneShippingAddressForOrder($user, $selectedShippingAddress);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending_payment',
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
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'payment_method' => 'momo',
                'transaction_id' => 'MOMO-SIM-' . now()->format('YmdHis') . '-' . $order->id,
                'status' => 'pending',
                'amount' => $total,
            ]);

            return [$order, $payment];
        });

        return response()->json([
            'message' => 'Da tao giao dich MoMo gia lap.',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount' => (float) $payment->amount,
            'redirect_url' => $this->frontendPageUrl('momo-sim.html', ['payment_id' => $payment->id]),
        ]);
    }

    public function show(Request $request, int $paymentId): JsonResponse
    {
        $payment = Payment::query()
            ->with(['order.items.product', 'order.shippingAddress'])
            ->find($paymentId);

        if (!$payment) {
            return response()->json(['message' => 'Khong tim thay giao dich.'], 404);
        }

        if (!$this->canAccessPayment($payment, $request->query('email', $request->input('email')))) {
            return response()->json(['message' => 'Ban khong co quyen xem giao dich nay.'], 403);
        }

        return response()->json([
            'data' => $payment,
        ]);
    }

    public function complete(Request $request, int $paymentId): JsonResponse
    {
        $payment = Payment::query()
            ->with(['order.items.product', 'order.shippingAddress'])
            ->find($paymentId);

        if (!$payment) {
            return response()->json(['message' => 'Khong tim thay giao dich.'], 404);
        }

        if (!$this->canAccessPayment($payment, $request->input('email', $request->query('email')))) {
            return response()->json(['message' => 'Ban khong co quyen cap nhat giao dich nay.'], 403);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Giao dich da thanh toan truoc do.',
                'data' => $payment->fresh(['order.items.product', 'order.shippingAddress']),
            ]);
        }

        foreach ($payment->order->items as $item) {
            $product = $item->product;
            if (!$product) {
                return response()->json(['message' => 'San pham trong don khong ton tai.'], 422);
            }

            if ((int) $product->stock < (int) $item->quantity) {
                return response()->json(['message' => $product->name . ' khong du ton kho de hoan tat giao dich.'], 422);
            }
        }

        try {
            DB::transaction(function () use ($payment) {
                foreach ($payment->order->items as $item) {
                    $product = $item->product;
                    $product->stock = max(0, (int) $product->stock - (int) $item->quantity);
                    $product->save();
                }

                $payment->status = 'completed';

                if (Schema::hasColumn('payments', 'paid_at')) {
                    $payment->paid_at = now();
                }

                if (Schema::hasColumn('payments', 'paid_ay')) {
                    $payment->paid_ay = now();
                }

                $payment->save();

                $payment->order->status = 'paid';
                $payment->order->save();

                CartItem::query()
                    ->where('user_id', $payment->order->user_id)
                    ->delete();
            });
        } catch (QueryException $exception) {
            return response()->json([
                'message' => 'Khong the hoan tat thanh toan MoMo luc nay. Vui long thu lai.',
            ], 422);
        }

        return response()->json([
            'message' => 'Thanh toan MoMo gia lap thanh cong.',
            'data' => $payment->fresh(['order.items.product', 'order.shippingAddress']),
        ]);
    }

    public function fail(Request $request, int $paymentId): JsonResponse
    {
        $payment = Payment::query()
            ->with(['order.items.product', 'order.shippingAddress'])
            ->find($paymentId);

        if (!$payment) {
            return response()->json(['message' => 'Khong tim thay giao dich.'], 404);
        }

        if (!$this->canAccessPayment($payment, $request->input('email', $request->query('email')))) {
            return response()->json(['message' => 'Ban khong co quyen cap nhat giao dich nay.'], 403);
        }

        $payment->status = 'failed';
        $payment->save();

        $payment->order->status = 'payment_failed';
        $payment->order->save();

        return response()->json([
            'message' => 'Da danh dau giao dich that bai.',
            'data' => $payment->fresh(['order.items.product', 'order.shippingAddress']),
        ]);
    }

    protected function canAccessPayment(Payment $payment, ?string $email): bool
    {
        $providedEmail = trim((string) $email);
        if ($providedEmail === '') {
            return true;
        }

        $user = $this->resolveCustomerByEmail($providedEmail);

        return $user && (int) $payment->order->user_id === (int) $user->id;
    }
}
