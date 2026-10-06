<?php

namespace App\Support;

use Illuminate\Mail\SentMessage;
use Resend;

/*
 * What Resend knows of an email the backoffice sent: the id its transport
 * stamps on the message, and the last thing it saw happen to it. Shared by
 * the bulk emails (reminder, day before, workshop reminder), so each keeps
 * the same evidence: who, when, and delivered / bounced / marked as spam.
 */
class Delivery
{
    /** Where Resend's story ends: nothing more to look up. */
    public const FINAL = ['delivered', 'bounced', 'complained', 'failed', 'canceled'];

    /** Resend's id for a sent message; null where mail is only logged (local). */
    public static function id(?SentMessage $sent): ?string
    {
        return $sent?->getOriginalMessage()->getHeaders()->get('X-Resend-Email-ID')?->getBodyAsString();
    }

    /** The status recorded right after sending: Resend has it, or it was only logged. */
    public static function initial(?string $id): string
    {
        return $id ? 'sent' : 'logged';
    }

    /** Resend's last event for the email — delivered, bounced, complained… */
    public static function lookup(string $id): ?string
    {
        return Resend::client(config('services.resend.key'))->emails->get($id)->last_event ?: null;
    }
}
