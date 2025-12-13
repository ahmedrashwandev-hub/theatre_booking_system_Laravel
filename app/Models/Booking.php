<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'movie_id',
        'user_id',
        'seat_number',
        'party_date',
        'party_number',
        'price',
        'extras'
    ];

    protected $casts = [
        'extras' => 'array',
        'party_date' => 'date',
    ];

    public function movie() {
        // return $this->hasMany(Movie::class);
        return $this->belongsTo(Movie::class);
    }

    public function user() {
        // return $this->hasMany(User::class);
        return $this->belongsTo(User::class);
    }
}
