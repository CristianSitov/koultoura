<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'registration_id', 'session_id', 'payment_intent_id',
        'email', 'amount', 'currency', 'status', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Record a paid checkout session, or update what we already hold for it.
     *
     * Keyed on the session id, so a Stripe retry and a later reconcile both
     * land on the same row rather than counting the money twice. Unpaid
     * sessions are ignored: an abandoned checkout is not a contribution.
     *
     * The registration is looked up but not required — the payment link is a
     * public URL, and a token can have been deleted since. Money that arrived
     * gets recorded either way.
     */
    public static function recordSession(object $session): ?self
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return null;
        }

        $registration = filled($session->client_reference_id ?? null)
            ? Registration::where('token', $session->client_reference_id)->first()
            : null;

        return static::updateOrCreate(
            ['session_id' => $session->id],
            [
                'registration_id' => $registration?->id,
                'payment_intent_id' => $session->payment_intent ?? null,
                'email' => $session->customer_details->email ?? null,
                'amount' => (int) ($session->amount_total ?? 0),
                'currency' => strtolower($session->currency ?? 'ron'),
                'status' => $session->payment_status,
                // Stripe's own timestamp where we have it: a row written by a
                // reconcile days later should not claim the money arrived today.
                'paid_at' => isset($session->created) ? date('Y-m-d H:i:s', $session->created) : now(),
            ]
        );
    }

    /** Stripe counts in minor units; people count in lei. */
    public function getMajorAmountAttribute(): float
    {
        return $this->amount / 100;
    }
}
