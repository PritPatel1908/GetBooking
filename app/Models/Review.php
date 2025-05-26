<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ground_id',
        'rating',
        'comment',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ground that the review is for.
     */
    public function ground()
    {
        return $this->belongsTo(Ground::class);
    }

    /**
     * Get the replies for this review.
     */
    public function replies()
    {
        return $this->hasMany(ReviewReply::class)->orderBy('created_at', 'asc');
    }
}
