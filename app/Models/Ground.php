<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'capacity',
        'ground_type',
        'ground_category',
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
    public function slots(): HasMany
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
    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Booking::class,
            BookingDetail::class,
            'ground_id', // Foreign key on booking_details table
            'id', // Foreign key on bookings table
            'id', // Local key on grounds table
            'booking_id' // Local key on booking_details table
        );
    }

    /**
     * Get the ground's active bookings.
     */
    public function activeBookings()
    {
        return $this->bookings()
            ->whereIn('bookings.booking_status', ['pending', 'confirmed'])
            ->where('bookings.booking_date', '>=', now()->format('Y-m-d'))
            ->orderBy('bookings.booking_date')
            ->orderBy('bookings.booking_time');
    }

    /**
     * Get the ground's completed bookings.
     */
    public function completedBookings()
    {
        return $this->bookings()
            ->where('bookings.booking_status', 'completed')
            ->orderBy('bookings.booking_date', 'desc')
            ->orderBy('bookings.booking_time', 'desc');
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
     * Get the reviews for the ground.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->orderBy('created_at', 'desc');
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

        // Check for any overlapping bookings through booking details
        $overlappingBookings = BookingDetail::where('ground_id', $this->id)
            ->whereHas('booking', function ($query) use ($date, $time, $startTime, $endTime) {
                $query->whereIn('booking_status', ['pending', 'confirmed'])
                    ->where('booking_date', $date)
                    ->where(function ($q) use ($time, $startTime, $endTime) {
                        $q->where(function ($subQ) use ($startTime, $endTime) {
                            // Check if the existing booking starts during our requested time
                            $subQ->whereRaw("STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') >= ?", [$startTime])
                                ->whereRaw("STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') < ?", [$endTime]);
                        })->orWhere(function ($subQ) use ($startTime, $endTime) {
                            // Check if the existing booking ends during our requested time
                            $timeFieldWithDuration = "STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i') + INTERVAL duration HOUR";
                            $subQ->whereRaw("$timeFieldWithDuration > ?", [$startTime])
                                ->whereRaw("$timeFieldWithDuration <= ?", [$endTime]);
                        })->orWhere(function ($subQ) use ($startTime, $endTime) {
                            // Check if the existing booking completely encompasses our requested time
                            $timeField = "STR_TO_DATE(CONCAT(booking_date, ' ', booking_time), '%Y-%m-%d %H:%i')";
                            $timeFieldWithDuration = "$timeField + INTERVAL duration HOUR";
                            $subQ->whereRaw("$timeField <= ?", [$startTime])
                                ->whereRaw("$timeFieldWithDuration >= ?", [$endTime]);
                        });
                    });
            })->count();

        return $overlappingBookings === 0;
    }
}
