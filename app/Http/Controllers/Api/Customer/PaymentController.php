<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CreatePaymentRequest;
use App\Http\Requests\Api\Customer\VerifyPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Throwable;

class PaymentController extends Controller
{
    public function createOrder(CreatePaymentRequest $request): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }


        if (strtolower(trim((string) $order->payment_status)) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This Order is not awaiting payment'
            ], 422);
        }

        if ((float)$order->total <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Order Amount'
            ], 422);
        }

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (!$key || !$secret) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay configuration is missing'
            ], 500);
        }

        try {
            $api = new  Api($key, $secret);

            $amountInPaise = (int) round((float)$order->total * 100);

            $razorpayOrder = $api->order->create([
                'receipt' => 'order_' . $order->id,
                'amount' => $amountInPaise,
                'currency' => 'INR',
            ]);


            $payment = Payment::create([
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $order->total,
                'currency' => 'INR',
                'status' => 'created',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'razorpay_key_id' => $key,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'order_id' => $order->id,
                ],
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Razorpay order: ' . $e->getMessage()
            ], 500);
        }
    }

    //verify payment
    public function verifyPayment(VerifyPaymentRequest $request)
    {
        $user = $request->user();

        $payment = Payment::where('gateway_order_id', $request->razorpay_order_id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found'
            ], 404);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment has already been verified'
            ]);
        }

        try {
            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            //

            $razorpayPayment = $api->payment->fetch(
                $request->razorpay_payment_id
            );

            if (
                $razorpayPayment['order_id'] !== $payment->gateway_order_id ||
                $razorpayPayment['status'] !== 'captured' ||
                (int) $razorpayPayment['amount'] !== (int) round((float) $payment->amount * 100) ||
                strtoupper($razorpayPayment['currency']) !== strtoupper($payment->currency)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not captured or payment details do not match.',
                ], 422);
            }
        } catch (SignatureVerificationError $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment signature.'
            ], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment: ' . $e->getMessage()
            ], 500);
        }

        DB::transaction(
            function () use ($payment, $request) {
                $payment->update([
                    'gateway_payment_id' => $request->razorpay_payment_id,
                    'status' => 'paid',
                ]);

                $payment->order->update([
                    'payment_status' => 'paid',
                ]);
            }
        );
        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully',
            'data' => [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
            ],
        ]);
    }
}
