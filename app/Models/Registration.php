<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * An attendee registration for the 2026 symposium.
 *
 * Pinned to the 2026 connection: the request middleware sets it for web
 * traffic, but console commands and queued mail run without that context.
 */
class Registration extends Model
{
    use HasFactory;

    protected $connection = 'wcm_2026';

    /*
     * The days a registration can pick, by their place in the programme:
     * 1–3 are 7–9 October. The 10th is the Heritage School's workshop day,
     * which has its own forms and is not registered for here.
     */
    public const DAYS = [1, 2, 3];

    protected $fillable = [
        'name', 'email', 'organisation', 'country', 'phone',
        'days', 'workshop_interest', 'locale', 'consented_at',
    ];

    protected $casts = [
        'days' => 'array',
        'workshop_interest' => 'boolean',
        'confirmed_at' => 'datetime',
        'consented_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $registration) {
            $registration->token ??= Str::random(48);
        });
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function confirm(): void
    {
        if (! $this->isConfirmed()) {
            $this->forceFill(['confirmed_at' => now()])->save();
        }
    }

    /**
     * A confirmation may be re-sent, but not on demand without limit — this is
     * the throttle behind the "send it again" link.
     */
    public function canResend(): bool
    {
        return $this->sent_count < 5
            && ($this->last_sent_at === null || $this->last_sent_at->diffInSeconds(now()) > 120);
    }

    public function markSent(): void
    {
        $this->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $this->sent_count + 1,
        ])->save();
    }

    /** Where this registration is confirmed, in the language it was made in. */
    public function confirmUrl(): string
    {
        $prefix = $this->locale === 'ro' ? '/ro' : '';

        return url(\App\Http\Controllers\Front2026Controller::base().$prefix.'/confirm/'.$this->token);
    }
}
