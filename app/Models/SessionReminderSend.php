<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One workshop reminder, to one booking or place, and what Resend last said about it. */
class SessionReminderSend extends Model
{
    protected $connection = 'wcm_2026';

    public $timestamps = false;

    protected $fillable = [
        'session_id', 'session_booking_id', 'session_place_id',
        'email', 'resend_id', 'status', 'error', 'sent_at', 'checked_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'checked_at' => 'datetime',
    ];
}
