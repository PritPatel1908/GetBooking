<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroundImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ground_id',
        'image_path',
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
    ];

    /**
     * Append these attributes to the model.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Get the formatted image URL.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // If it starts with 'http', return as is
        if (strpos($this->image_path, 'http') === 0) {
            return $this->image_path;
        }

        // Otherwise, assume it's a local path and generate a full URL
        return asset($this->image_path);
    }

    /**
     * Get the ground that owns the image.
     */
    public function ground(): BelongsTo
    {
        return $this->belongsTo(Ground::class);
    }
}
