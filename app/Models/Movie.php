<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'description',
        'poster',
        'TypeOfFilm',
        'duration',
    ];

    // Append a computed attribute for a full poster URL
    protected $appends = ['poster_url'];

    public function getPosterUrlAttribute()
    {
        if (!$this->poster) {
            return null;
        }

        // Use the public storage path (public/storage/<path>) for access
        return asset('storage/' . $this->poster);
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }
}
