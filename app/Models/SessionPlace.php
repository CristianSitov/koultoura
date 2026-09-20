<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** One seat in an internal workshop, handed out by the office. */
class SessionPlace extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'session_id', 'code', 'email', 'token', 'status', 'invited_at', 'confirmed_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /*
     * Uppercase letters and digits, minus the look-alikes (I, O, 0, 1) so a
     * code reads cleanly whether typed or read aloud.
     */
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected static function booted(): void
    {
        static::creating(function (self $place) {
            $place->token ??= Str::random(48);
            $place->code ??= self::freshCode();
        });
    }

    /** A six-character code not already taken. */
    public static function freshCode(): string
    {
        do {
            $code = collect(range(1, 6))
                ->map(fn () => self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)])
                ->implode('');
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
