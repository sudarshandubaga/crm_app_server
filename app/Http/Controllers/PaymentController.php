<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private $api;

    public function __construct()
    {
        $this->api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $firm = $request->user()->firm;

        if ($plan->is_first_time_only && $firm->has_used_first_time_offer) {
            return response()->json(['message' => 'This plan is only available for first-time users.'], 403);
        }

        $orderData = [
            'receipt'         => 'rcpt_' . uniqid(),
            'amount'          => $plan->amount * 100, // in paise
            'currency'        => 'INR',
        ];

        $razorpayOrder = $this->api->order->create($orderData);

        $payment = Payment::create([
            'firm_id' => $firm->id,
            'plan_id' => $plan->id,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $plan->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount' => $plan->amount,
            'key_id' => config('services.razorpay.key_id'),
            'firm_name' => $firm->name,
            'user_email' => $request->user()->email,
            'user_mobile' => $request->user()->mobile,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];

        try {
            $this->api->utility->verifyPaymentSignature($attributes);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment verification failed.'], 400);
        }

        $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'success',
            ]);

            $firm = $payment->firm;
            $plan = $payment->plan;

            // Update firm subscription
            $currentExpiry = $firm->expire_at && $firm->expire_at->isFuture() ? $firm->expire_at : Carbon::now();
            $newExpiry = $currentExpiry->addMonths($plan->duration_months);

            $firm->update([
                'plan_id' => $plan->id,
                'expire_at' => $newExpiry,
                'has_used_first_time_offer' => true,
            ]);
        });

        return response()->json(['message' => 'Payment successful.']);
    }
}
