<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_sku',
        'user_id',
        'booking_date',
        'amount',
        'booking_status',
        'payment_status',
        'notes',
        'payment_id',
        'slot_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'user_id' => 'integer',
        'payment_id' => 'integer',
        'slot_id' => 'integer',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the slot associated with the booking.
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(GroundSlot::class, 'slot_id');
    }

    /**
     * Get the payment associated with the booking.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Get the payment via booking_id (alternative relationship).
     */
    public function paymentByBookingId(): HasOne
    {
        return $this->hasOne(Payment::class, 'booking_id');
    }

    /**
     * Get the booking details for this booking.
     */
    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    /**
     * Get the grounds associated with the booking through booking details.
     */
    public function grounds()
    {
        return $this->hasManyThrough(
            Ground::class,
            BookingDetail::class,
            'booking_id', // Foreign key on booking_details table
            'id', // Foreign key on grounds table
            'id', // Local key on bookings table
            'ground_id' // Local key on booking_details table
        );
    }

    /**
     * Get the first ground associated with the booking.
     */
    public function ground()
    {
        return $this->hasOneThrough(
            Ground::class,
            BookingDetail::class,
            'booking_id', // Foreign key on booking_details table
            'id', // Foreign key on grounds table
            'id', // Local key on bookings table
            'ground_id' // Local key on booking_details table
        );
    }

    /**
     * Get the total duration from booking details.
     */
    public function getTotalDurationAttribute()
    {
        return $this->details->sum('duration');
    }

    /**
     * Get the booking time from booking details.
     */
    public function getBookingTimeAttribute()
    {
        return $this->details->pluck('time_slot')->implode(', ');
    }

    /**
     * Get the first booking time for backward compatibility.
     */
    public function getFirstBookingTimeAttribute()
    {
        return $this->details->first()?->time_slot ?? '';
    }
}
