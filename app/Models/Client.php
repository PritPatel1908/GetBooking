<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'phone',
        'gender',
        'full_address',
        'area',
        'city',
        'pincode',
        'state',
        'country',
        'profile_picture',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the grounds for the client.
     */
    public function grounds(): HasMany
    {
        return $this->hasMany(Ground::class);
    }

    /**
     * Get the full name of the client.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        $name = $this->first_name;

        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }

        if ($this->last_name) {
            $name .= ' ' . $this->last_name;
        }

        return $name;
    }

    /**
     * Get the profile picture URL.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset($this->profile_picture);
        }

        return asset('assets/images/default-avatar.png');
    }
}
