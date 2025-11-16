<?php

namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Models\Client;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\GroundImage;
use App\Models\GroundFeature;
use App\Models\GroundSlot;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function dashboard()
    {
        // Check if user is not client - only clients can access client panel
        if (Auth::user()->user_type !== 'client') {
            return redirect()->route('user.home');
        }

        $client = Auth::user()->client;
        if (!$client) {
            // If client relationship doesn't exist, redirect to user home
            return redirect()->route('user.home')->with('error', 'Client profile not found.');
        }

        // Get client's grounds
        $grounds = Ground::where('client_id', $client->id)->get();
        $groundIds = $grounds->pluck('id');

        // Get bookings for client's grounds
        $bookings = Booking::whereHas('details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })->orderBy('created_at', 'desc')->limit(5)->get();

        // Get payments for client's grounds
        $payments = Payment::whereHas('booking.details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })->orderBy('created_at', 'desc')->limit(5)->get();

        // Get statistics
        $totalGrounds = $grounds->count();
        $totalBookings = Booking::whereHas('details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })->count();
        $totalRevenue = Payment::whereHas('booking.details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })->where('payment_status', 'completed')->sum('amount');
        $totalUsers = User::where('user_type', 'user')->count();

        return view('client.dashboard', compact('grounds', 'bookings', 'payments', 'totalGrounds', 'totalBookings', 'totalRevenue', 'totalUsers'));
    }

    public function grounds(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $grounds = Ground::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Enrich each ground with bookings count and last booking date
        foreach ($grounds as $ground) {
            $bookingsCount = Booking::whereHas('details', function ($query) use ($ground) {
                $query->where('ground_id', $ground->id);
            })->count();

            $lastBookingCreated = Booking::whereHas('details', function ($query) use ($ground) {
                $query->where('ground_id', $ground->id);
            })
                ->orderBy('created_at', 'desc')
                ->value('created_at');

            $ground->bookings_count = $bookingsCount;
            $ground->last_booking_date = $lastBookingCreated ? $lastBookingCreated->format('d M Y') : 'No bookings';
        }

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            $formattedGrounds = $grounds->map(function ($ground) {
                return [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'price_per_hour' => $ground->price_per_hour,
                    'status' => $ground->status,
                    'created_at' => $ground->created_at->format('d M Y'),
                    'bookings_count' => $ground->bookings_count ?? 0,
                    'last_booking_date' => $ground->last_booking_date ?? 'No bookings'
                ];
            });

            return response()->json([
                'grounds' => $formattedGrounds,
                'pagination' => [
                    'total' => $grounds->total(),
                    'per_page' => $grounds->perPage(),
                    'current_page' => $grounds->currentPage(),
                    'last_page' => $grounds->lastPage(),
                    'from' => $grounds->firstItem(),
                    'to' => $grounds->lastItem()
                ]
            ]);
        }

        return view('client.grounds', compact('grounds'));
    }

    public function grounds_pagination(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $grounds = Ground::where('client_id', $client->id)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('client.partials.grounds-table', compact('grounds'))->render();
    }

    public function ground_view_page($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $ground = Ground::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$ground) {
            abort(404, 'Ground not found or you do not have permission to view it.');
        }

        return view('client.ground-view', compact('ground'));
    }

    public function bookings(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $bookings = Booking::whereHas('details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->with(['user', 'details.ground'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($request->ajax() || $request->has('ajax')) {
            $formattedBookings = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'user_name' => $booking->user->name,
                    'user_email' => $booking->user->email,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'booking_date' => $booking->booking_date,
                    'created_at' => $booking->created_at->format('d M Y'),
                    'grounds' => $booking->details->map(function ($detail) {
                        return [
                            'ground_name' => $detail->ground->name,
                            'slot_time' => $detail->slot_time,
                            'duration' => $detail->duration
                        ];
                    })
                ];
            });

            return response()->json([
                'bookings' => $formattedBookings,
                'pagination' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem()
                ]
            ]);
        }

        return view('client.bookings', compact('bookings'));
    }

    public function bookings_pagination(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $bookings = Booking::whereHas('details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->with(['user', 'details.ground'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('client.partials.bookings-table', compact('bookings'))->render();
    }

    public function booking_view_page($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $booking = Booking::whereHas('details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->with(['user', 'details.ground', 'payment'])
            ->find($id);

        if (!$booking) {
            abort(404, 'Booking not found or you do not have permission to view it.');
        }

        return view('client.booking-view', compact('booking'));
    }

    public function payments(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $payments = Payment::whereHas('booking.details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->with(['booking.user', 'booking.details.ground'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($request->ajax() || $request->has('ajax')) {
            $formattedPayments = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_id' => $payment->id,
                    'user_name' => $payment->booking->user->name,
                    'amount' => $payment->amount,
                    'status' => $payment->payment_status,
                    'payment_method' => $payment->payment_method,
                    'created_at' => $payment->created_at->format('d M Y'),
                    'booking_sku' => $payment->booking->booking_sku
                ];
            });

            return response()->json([
                'payments' => $formattedPayments,
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem()
                ]
            ]);
        }

        return view('client.payments', compact('payments'));
    }

    public function payments_pagination(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $payments = Payment::whereHas('booking.details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->whereHas('booking.user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->with(['booking.user', 'booking.details.ground'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('client.partials.payments-table', compact('payments'))->render();
    }

    public function payment_view_page($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $groundIds = Ground::where('client_id', $client->id)->pluck('id');

        $payment = Payment::whereHas('booking.details', function ($query) use ($groundIds) {
            $query->whereIn('ground_id', $groundIds);
        })
            ->with(['booking.user', 'booking.details.ground'])
            ->find($id);

        if (!$payment) {
            abort(404, 'Payment not found or you do not have permission to view it.');
        }

        return view('client.payment-view', compact('payment'));
    }

    // Ground CRUD operations
    public function ground_create(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_hour' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'features' => 'array',
            'features.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $ground = Ground::create([
                'name' => $request->name,
                'location' => $request->location,
                'description' => $request->description,
                'price_per_hour' => $request->price_per_hour,
                'capacity' => $request->capacity,
                'client_id' => $client->id,
                'status' => 'active'
            ]);

            // Add features
            if ($request->has('features') && is_array($request->features)) {
                foreach ($request->features as $feature) {
                    if (!empty($feature)) {
                        GroundFeature::create([
                            'ground_id' => $ground->id,
                            'feature' => $feature
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ground created successfully',
                'ground' => $ground
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ground creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ground'
            ], 500);
        }
    }

    public function ground_delete($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $ground = Ground::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$ground) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found or you do not have permission to delete it.'
            ], 404);
        }

        try {
            // Delete related records
            GroundFeature::where('ground_id', $ground->id)->delete();
            GroundImage::where('ground_id', $ground->id)->delete();
            GroundSlot::where('ground_id', $ground->id)->delete();

            $ground->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ground deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Ground deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ground'
            ], 500);
        }
    }

    public function ground_edit($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $ground = Ground::where('id', $id)
            ->where('client_id', $client->id)
            ->with('features')
            ->first();

        if (!$ground) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found or you do not have permission to edit it.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ground' => $ground
        ]);
    }

    public function ground_view($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $ground = Ground::where('id', $id)
            ->where('client_id', $client->id)
            ->with(['features', 'images'])
            ->first();

        if (!$ground) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found or you do not have permission to view it.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ground' => $ground
        ]);
    }

    public function ground_image_delete($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $image = GroundImage::where('id', $id)
            ->whereHas('ground', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found or you do not have permission to delete it.'
            ], 404);
        }

        try {
            // Delete file from storage
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Image deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image'
            ], 500);
        }
    }

    public function ground_image_upload(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify ground belongs to client
        $ground = Ground::where('id', $request->ground_id)
            ->where('client_id', $client->id)
            ->first();

        if (!$ground) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found or you do not have permission to upload images to it.'
            ], 404);
        }

        try {
            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('uploads/grounds', $filename, 'public');

                $groundImage = GroundImage::create([
                    'ground_id' => $ground->id,
                    'image_path' => $path,
                    'alt_text' => $ground->name . ' image'
                ]);

                $uploadedImages[] = $groundImage;
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'images' => $uploadedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images'
            ], 500);
        }
    }

    public function ground_image_upload_page($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $ground = Ground::where('id', $id)
            ->where('client_id', $client->id)
            ->with('images')
            ->first();

        if (!$ground) {
            abort(404, 'Ground not found or you do not have permission to view it.');
        }

        return view('client.ground-image-upload', compact('ground'));
    }
}
