<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One reminder, to one person, and what Resend last said about it. */
class ReminderSend extends Model
{
    protected $connection = 'wcm_2026';

    public $timestamps = false;

    protected $fillable = ['reminder_id', 'registration_id', 'email', 'resend_id', 'status', 'error', 'sent_at', 'checked_at'];

    protected $casts = [
        'sent_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    /** Where Resend's story ends: nothing more to look up. */
    public const FINAL = ['delivered', 'bounced', 'complained', 'failed', 'canceled'];
}
