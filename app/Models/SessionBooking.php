<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** A place taken in a capped session. */
class SessionBooking extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'session_id', 'registration_id', 'token', 'name', 'email',
        'phone', 'locale', 'confirmed_at', 'cancelled_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            $booking->token ??= Str::random(48);
        });
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }
}
