<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Process a payment for a booking
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to make a payment.'
                ], 401);
            }

            // Validate request
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'payment_method' => 'required|string',
                'amount' => 'required|numeric|min:1',
            ]);

            // Get the booking
            $booking = Booking::findOrFail($validated['booking_id']);

            // Check if this user owns this booking
            if ($booking->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to pay for this booking.'
                ], 403);
            }

            // Check if booking is pending
            if ($booking->booking_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking is not in pending status and cannot be paid for.'
                ], 400);
            }

            // Process payment - in a real application, you would integrate with a payment gateway here

            // For demo, we'll simulate a successful payment
            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => Auth::id(),
                    'date' => now(),
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'completed',
                    'payment_type' => 'booking',
                    'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                    'payment_response' => json_encode([
                        'method' => $validated['payment_method'],
                        'amount' => $validated['amount'],
                        'status' => 'completed',
                        'transaction_date' => now()->format('Y-m-d H:i:s'),
                        'user_id' => Auth::id()
                    ], JSON_PRETTY_PRINT)
                ]
            );

            // Update booking status
            $booking->booking_status = 'confirmed';
            $booking->payment_id = $payment->id;
            $booking->save();

            Log::info("Payment processed successfully for booking ID: {$booking->id}, Payment ID: {$payment->id}");

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'booking' => $booking,
                'payment' => $payment,
                'redirect_url' => route('user.my_bookings')
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment from callback (after redirect from payment gateway)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentCallback(Request $request)
    {
        try {
            // Get the booking ID from the request
            $bookingId = $request->booking_id;
            $transactionId = $request->transaction_id;
            $status = $request->status;

            // Check if we have all required parameters
            if (!$bookingId || !$transactionId || !$status) {
                return redirect()->route('user.my_bookings')
                    ->with('error', 'Invalid payment callback data received.');
            }

            // Get the booking
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return redirect()->route('user.my_bookings')
                    ->with('error', 'Booking not found.');
            }

            // Update payment status
            $payment = Payment::where('booking_id', $bookingId)->first();
            if (!$payment) {
                // Create payment record if it doesn't exist
                $payment = new Payment([
                    'booking_id' => $bookingId,
                    'user_id' => $booking->user_id,
                    'date' => now(),
                    'amount' => $booking->amount,
                    'payment_method' => 'online',
                    'payment_type' => 'booking',
                    'transaction_id' => $transactionId
                ]);
            }

            // Update payment status based on callback
            if ($status === 'success') {
                $payment->payment_status = 'completed';
                $booking->booking_status = 'confirmed';
                $message = 'Payment successful!';
            } else {
                $payment->payment_status = 'failed';
                $message = 'Payment failed. Please try again.';
            }

            // Save payment and booking updates
            $payment->payment_response = json_encode($request->all());
            $payment->save();

            // Update booking with payment ID
            $booking->payment_id = $payment->id;
            $booking->save();

            // Redirect to my bookings page with appropriate message
            return redirect()->route('user.my_bookings')
                ->with($status === 'success' ? 'success' : 'error', $message);
        } catch (\Exception $e) {
            Log::error('Error in payment callback: ' . $e->getMessage());
            return redirect()->route('user.my_bookings')
                ->with('error', 'An error occurred processing your payment. Please contact support.');
        }
    }

    public function pendingPayments()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->with(['booking' => function ($query) {
                $query->with(['details' => function ($query) {
                    $query->with('ground');
                }]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalAmountPaid = $payments->where('payment_status', 'completed')->sum('amount');
        $totalPaymentsCount = $payments->count();
        $totalRefundedAmount = $payments->where('payment_status', 'refunded')->sum('amount');
        $totalFailedAmount = $payments->where('payment_status', 'failed')->sum('amount');

        // Return React app view for SPA
        return view('user.react-app');
    }

    public function viewTransaction($id)
    {
        $transaction = Payment::where('user_id', Auth::id())
            ->where('id', $id)
            ->with(['booking' => function ($query) {
                $query->with(['details' => function ($query) {
                    $query->with('ground');
                }]);
            }])
            ->firstOrFail();

        // Return React app view for SPA
        return view('user.react-app');
    }
}
