<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Someone the internal agenda is sent to: a speaker, a guest, one of the team. */
class AgendaRecipient extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = ['name', 'email', 'locale', 'token', 'last_sent_at', 'sent_count'];

    protected $casts = [
        'last_sent_at' => 'datetime',
        'sent_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $recipient) {
            $recipient->token ??= Str::random(48);
        });
    }
}
