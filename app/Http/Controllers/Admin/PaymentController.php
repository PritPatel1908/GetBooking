<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    protected $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Process refund for a payment
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function processRefund(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'reason' => 'required|string',
                'other_reason' => 'required_if:reason,other|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the payment
            $payment = Payment::with('booking', 'user')->findOrFail($id);

            // Check if payment is already refunded
            if ($payment->payment_status === 'refunded') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment has already been refunded'
                ], 400);
            }

            // Check if payment is completed
            if ($payment->payment_status !== 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only completed payments can be refunded'
                ], 400);
            }

            // Get refund reason
            $refundReason = $request->reason;
            if ($refundReason === 'other' && $request->has('other_reason')) {
                $refundReason = $request->other_reason;
            }

            // Process normal refund through Razorpay API (not instant)
            $refund = $this->razorpay->payment->fetch($payment->transaction_id)->refund([
                'amount' => $request->amount * 100, // Convert to paisa (Razorpay uses smallest currency unit)
                'speed' => 'normal', // Use normal refund instead of instant
                'notes' => [
                    'reason' => $refundReason,
                    'booking_id' => $payment->booking ? $payment->booking->id : null,
                    'refunded_by' => 'admin',
                    'refunded_at' => now()->format('Y-m-d H:i:s')
                ]
            ]);

            // Update payment status
            $payment->payment_status = 'refunded';
            $payment->payment_response_data_json = json_encode([
                'refund_id' => $refund->id,
                'refund_amount' => $request->amount,
                'refund_reason' => $refundReason,
                'refund_date' => now()->format('Y-m-d H:i:s'),
                'refund_status' => $refund->status,
                'refund_speed' => 'normal',
                'original_payment' => json_decode($payment->payment_response_data_json ?? '{}')
            ]);
            $payment->save();

            // Update booking status if booking exists
            if ($payment->booking) {
                $booking = $payment->booking;
                $booking->booking_status = 'cancelled';
                $booking->cancellation_reason = 'Payment refunded: ' . $refundReason;
                $booking->cancelled_at = now();
                $booking->save();
            }

            // Log the refund
            Log::info("Payment refund initiated successfully", [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'refund_id' => $refund->id,
                'amount' => $request->amount,
                'reason' => $refundReason,
                'speed' => 'normal'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment refund initiated successfully',
                'refund' => [
                    'id' => $refund->id,
                    'amount' => $refund->amount / 100, // Convert back from paisa
                    'status' => $refund->status,
                    'speed' => 'normal',
                    'created_at' => $refund->created_at
                ]
            ]);
        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('Razorpay refund error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Razorpay error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error processing refund: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }
}
