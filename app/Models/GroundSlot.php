<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroundSlot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ground_id',
        'slot_name',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_type',
        'slot_status',
        'price_per_slot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'ground_id' => 'int',
        'start_time' => 'string',
        'end_time' => 'string',
        'price_per_slot' => 'decimal:2',
    ];

    /**
     * Get the ground that owns the slot.
     */
    public function ground(): BelongsTo
    {
        return $this->belongsTo(Ground::class);
    }

    /**
     * Get the bookings for this slot.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }

    /**
     * Get formatted slot time range
     *
     * @return string
     */
    public function getTimeRangeAttribute(): string
    {
        if ($this->start_time && $this->end_time) {
            // If start_time and end_time are already formatted strings, use them directly
            // Otherwise, format them from time/datetime
            $start = is_string($this->start_time) ? $this->start_time : date('H:i', strtotime($this->start_time));
            $end = is_string($this->end_time) ? $this->end_time : date('H:i', strtotime($this->end_time));
            return $start . ' - ' . $end;
        }

        return $this->slot_name;
    }
}
