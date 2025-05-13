<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ground_id',
        'slot_name',
        'slot_status',
    ];

    /**
     * Get the ground that this slot belongs to.
     */
    public function ground()
    {
        return $this->belongsTo(Ground::class);
    }

    /**
     * Get the bookings for this slot.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the slot is available on a specific date.
     *
     * @param string $date Date in Y-m-d format
     * @return bool
     */
    public function isAvailable($date)
    {
        // First check if slot is active
        if ($this->slot_status !== 'active') {
            return false;
        }

        // Check if this slot is booked on the specified date
        $isBooked = $this->bookings()
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->exists();

        return !$isBooked;
    }
}
