<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * The one email that matters: it carries the link that turns a pending
 * registration into a confirmed one. Sent the moment the form is submitted,
 * before any payment step, so an address is never taken on trust.
 */
class RegistrationConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Registration $registration, public string $confirmUrl)
    {
    }

    public function envelope(): Envelope
    {
        // Written in whichever language they registered in.
        App::setLocale($this->registration->locale);

        return new Envelope(subject: __('Confirm your registration · Why Culture Matters 2026'));
    }

    public function content(): Content
    {
        App::setLocale($this->registration->locale);

        return new Content(markdown: 'emails.registration-confirmation');
    }
}
