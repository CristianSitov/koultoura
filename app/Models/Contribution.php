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

    /** Stripe counts in minor units; people count in lei. */
    public function getMajorAmountAttribute(): float
    {
        return $this->amount / 100;
    }
}
