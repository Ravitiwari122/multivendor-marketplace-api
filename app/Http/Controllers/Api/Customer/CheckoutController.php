<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CheckoutRequest;
use App\Models\Address;
use App\Models\Cart;

class CheckoutController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        $user = $request->user();

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cartItems = Cart::where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $subtotal = 0;

        foreach ($cartItems as $item) {

            if (!$item->product || !$item->product->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more products are unavailable.',
                ], 422);
            }

            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$item->product->name}.",
                ], 422);
            }

            $price = $item->product->discount_price
                ?? $item->product->price;

            $subtotal += $price * $item->quantity;
        }

        $shipping = 0;

        $total = $subtotal + $shipping;

        return response()->json([
            'success' => true,
            'message' => 'Checkout details calculated successfully.',
            'data' => [
                'address' => $address,
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
            ],
        ]);
    }
}
