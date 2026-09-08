<?php

namespace App\Mail;

use App\Http\Controllers\Front2026Controller;
use App\Models\Registration;
use App\Support\SymposiumCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * Sent once the address has been confirmed: the dates in writing, a way into
 * the programme, and the event as a calendar entry.
 *
 * The first email asks for something. This one gives something back, and is
 * the last they hear from us until the programme is settled.
 */
class RegistrationConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Registration $registration)
    {
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->registration->locale);

        return new Envelope(subject: __('You are registered · Why Culture Matters 2026'));
    }

    public function content(): Content
    {
        App::setLocale($this->registration->locale);

        $locale = $this->registration->locale;

        return new Content(
            markdown: 'emails.registration-confirmed',
            with: [
                'programmeUrl' => url(Front2026Controller::base().($locale === 'ro' ? '/ro' : '').'#programme'),
                'googleCalendarUrl' => SymposiumCalendar::googleUrl($locale),
            ],
        );
    }

    /** The event itself, for the calendars that are not Google's. */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => SymposiumCalendar::ics($this->registration->locale),
                'why-culture-matters-2026.ics'
            )->withMime('text/calendar; charset=UTF-8; method=PUBLISH'),
        ];
    }
}
