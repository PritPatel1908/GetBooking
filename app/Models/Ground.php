<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Ground extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'location',
        'price_per_hour',
        'capacity',
        'ground_type',
        'description',
        'rules',
        'opening_time',
        'closing_time',
        'phone',
        'email',
        'status',
        'client_id',
        'is_new',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'capacity' => 'int',
        'client_id' => 'int',
        'rules' => 'string',
        'ground_type' => 'string',
        'status' => 'string',
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client that owns the ground.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the images for the ground.
     */
    public function images(): HasMany
    {
        return $this->hasMany(GroundImage::class);
    }

    /**
     * Get the slots for this ground.
     */
    public function slots()
    {
        return $this->hasMany(GroundSlot::class);
    }

    /**
     * Get the features for the ground.
     */
    public function features(): HasMany
    {
        return $this->hasMany(GroundFeature::class);
    }

    /**
     * Get the bookings for the ground.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the ground's active bookings.
     */
    public function activeBookings()
    {
        return $this->bookings()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', now()->format('Y-m-d'))
            ->orderBy('booking_date')
            ->orderBy('booking_time');
    }

    /**
     * Get the ground's completed bookings.
     */
    public function completedBookings()
    {
        return $this->bookings()
            ->where('booking_status', 'completed')
            ->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc');
    }

    /**
     * Check if the ground has any slots.
     */
    public function hasSlots(): bool
    {
        return $this->slots()->count() > 0;
    }

    /**
     * Check if the ground has any features.
     */
    public function hasFeatures(): bool
    {
        return $this->features()->count() > 0;
    }

    /**
     * Get the ground's primary image URL.
     */
    public function getImageUrl(): string
    {
        if ($this->ground_image) {
            return asset($this->ground_image);
        }

        // Get the first image from the images relationship, if any
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return asset($firstImage->image_path);
        }

        // Return a placeholder image
        return asset('assets/images/ground-placeholder.jpg');
    }

    /**
     * Scope a query to only include active grounds.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the ground is available on a specific date and time.
     *
     * @param string $date The date in Y-m-d format
     * @param string $time The time in H:i format
     * @param int $duration The duration in hours
     * @return bool
     */
    public function isAvailable(string $date, string $time, int $duration = 1): bool
    {
        // Convert the requested time to a Carbon instance
        $startTime = \Carbon\Carbon::parse("$date $time");
        $endTime = (clone $startTime)->addHours($duration);

        // Check if the ground is closed at this time
        if ($this->opening_time && $this->closing_time) {
            $groundOpenTime = \Carbon\Carbon::parse("$date {$this->opening_time}");
            $groundCloseTime = \Carbon\Carbon::parse("$date {$this->closing_time}");

            if ($startTime < $groundOpenTime || $endTime > $groundCloseTime) {
                return false;
            }
        }

        // Check for any overlapping bookings
        $overlappingBookings = $this->bookings()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('booking_date', $date)
            ->where(function ($query) use ($time, $startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Check if the existing booking starts during our requested time
                    $q->whereRaw("STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') >= ?", [$startTime])
                        ->whereRaw("STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') < ?", [$endTime]);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // Check if the existing booking ends during our requested time
                    $timeFieldWithDuration = "STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') + INTERVAL duration HOUR";
                    $q->whereRaw("$timeFieldWithDuration > ?", [$startTime])
                        ->whereRaw("$timeFieldWithDuration <= ?", [$endTime]);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // Check if the existing booking completely encompasses our requested time
                    $timeField = "STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i')";
                    $timeFieldWithDuration = "$timeField + INTERVAL duration HOUR";
                    $q->whereRaw("$timeField <= ?", [$startTime])
                        ->whereRaw("$timeFieldWithDuration >= ?", [$endTime]);
                });
            })->count();

        return $overlappingBookings === 0;
    }
}
