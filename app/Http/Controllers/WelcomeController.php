<?php

namespace App\Http\Controllers;

use App\Models\Ground;
use App\Models\GroundFeature;
use App\Models\GroundImage;
use App\Models\GroundSlot;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Return React app view for SPA
        return view('user.react-app');
    }

    public function showGround($id)
    {
        // Return React app view for SPA
        return view('user.react-app');
    }

    public function getGroundSlots($id, Request $request)
    {
        $date = $request->date;
        $ground = Ground::with(['slots' => function ($query) use ($date) {
            $query->whereDoesntHave('bookings', function ($q) use ($date) {
                $q->where('booking_date', $date)
                    ->whereIn('booking_status', ['pending', 'confirmed']);
            });
        }])->findOrFail($id);

        return response()->json([
            'slots' => $ground->slots
        ]);
    }

    public function getBookingSummary(Request $request)
    {
        $ground = Ground::findOrFail($request->ground_id);
        $slot = GroundSlot::findOrFail($request->slot_id);

        $summary = [
            'ground_name' => $ground->name,
            'slot_name' => $slot->slot_name,
            'date' => $request->date,
            'time' => $request->time,
            'duration' => $request->duration,
            'amount' => $slot->price_per_slot * $request->duration
        ];

        return response()->json($summary);
    }
}
