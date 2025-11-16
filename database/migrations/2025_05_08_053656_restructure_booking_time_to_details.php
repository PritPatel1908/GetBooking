<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add slot_id if it doesn't exist
        Schema::table('booking_details', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_details', 'slot_id')) {
                $table->foreignId('slot_id')->nullable()->after('ground_id')->constrained('ground_slots')->onDelete('cascade');
            }
        });

        // Then, add new fields to booking_details table
        Schema::table('booking_details', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_details', 'booking_time')) {
                $table->string('booking_time')->nullable()->after('slot_id');
            }
            if (!Schema::hasColumn('booking_details', 'duration')) {
                $table->integer('duration')->nullable()->after('booking_time');
            }
            if (!Schema::hasColumn('booking_details', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('duration');
            }
            if (!Schema::hasColumn('booking_details', 'time_slot')) {
                $table->string('time_slot')->nullable()->after('price');
            }
        });

        // Migrate existing data from bookings to booking_details
        $bookings = DB::table('bookings')->get();

        foreach ($bookings as $booking) {
            // Get booking details for this booking
            $bookingDetails = DB::table('booking_details')
                ->where('booking_id', $booking->id)
                ->get();

            if ($bookingDetails->count() > 0) {
                // If there are multiple details, distribute the booking_time and duration
                $timeSlots = explode(',', $booking->booking_time);
                $totalDuration = $booking->duration;
                $durationPerSlot = $bookingDetails->count() > 0 ? $totalDuration / $bookingDetails->count() : $totalDuration;

                foreach ($bookingDetails as $index => $detail) {
                    $timeSlot = isset($timeSlots[$index]) ? trim($timeSlots[$index]) : $booking->booking_time;

                    DB::table('booking_details')
                        ->where('id', $detail->id)
                        ->update([
                            'booking_time' => $timeSlot,
                            'duration' => round($durationPerSlot),
                            'time_slot' => $timeSlot,
                            'price' => $booking->amount / $bookingDetails->count()
                        ]);
                }
            } else {
                // If no booking details exist, create one
                DB::table('booking_details')->insert([
                    'booking_id' => $booking->id,
                    'ground_id' => 1, // Default ground, should be updated based on actual data
                    'booking_time' => $booking->booking_time,
                    'duration' => $booking->duration,
                    'time_slot' => $booking->booking_time,
                    'price' => $booking->amount,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Now remove booking_time and duration from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_time', 'duration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back booking_time and duration to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_time')->after('booking_date');
            $table->integer('duration')->after('booking_time');
        });

        // Migrate data back from booking_details to bookings
        $bookings = DB::table('bookings')->get();

        foreach ($bookings as $booking) {
            $bookingDetails = DB::table('booking_details')
                ->where('booking_id', $booking->id)
                ->get();

            if ($bookingDetails->count() > 0) {
                $timeSlots = $bookingDetails->pluck('time_slot')->toArray();
                $totalDuration = $bookingDetails->sum('duration');
                $totalPrice = $bookingDetails->sum('price');

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update([
                        'booking_time' => implode(', ', $timeSlots),
                        'duration' => $totalDuration,
                        'amount' => $totalPrice
                    ]);
            }
        }

        // Remove the new fields from booking_details
        Schema::table('booking_details', function (Blueprint $table) {
            $table->dropColumn(['booking_time', 'duration', 'price', 'time_slot']);
        });
    }
};
