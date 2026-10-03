<?php

namespace App\Mail;

use App\Http\Controllers\Front2026Controller;
use App\Models\SessionBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

/**
 * A workshop or tour place, in writing — which one, when — and a request: the
 * link opens the booking page, where the place is confirmed or released.
 *
 * Sent by hand from Workshops (one person, or everyone not yet confirmed — a
 * second send is the reminder), and on booking when the workshop's
 * "send automatically" switch is on. Always through `sendTo`, which records it.
 */
class BookingConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public SessionBooking $booking)
    {
    }

    /** Sends it, and notes when — the Workshops screen shows who was asked. */
    public static function sendTo(SessionBooking $booking): void
    {
        Mail::to($booking->email)->send(new self($booking->loadMissing('session.translations', 'session.day')));

        $booking->forceFill([
            'confirmation_sent_at' => now(),
            'confirmation_sent_count' => $booking->confirmation_sent_count + 1,
        ])->save();
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->language());

        return new Envelope(subject: __('booking.email.subject', ['title' => $this->title()]));
    }

    public function content(): Content
    {
        $locale = $this->language();
        App::setLocale($locale);

        $session = $this->booking->session;

        return new Content(markdown: 'emails.booking-confirmed', with: [
            'name' => $this->booking->first_name ?: $this->booking->name,
            'title' => $this->title(),
            'date' => $session->day?->date->locale($locale)->translatedFormat('l, j F'),
            'time' => substr($session->starts_at, 0, 5),
            // The booking's own page, in the language it was made in.
            'url' => url(Front2026Controller::base()
                .($locale === 'ro' ? '/ro' : '')
                .'/sessions/'.$session->slug.'/booked/'.$this->booking->token),
        ]);
    }

    private function language(): string
    {
        return $this->booking->locale === 'ro' ? 'ro' : 'en';
    }

    private function title(): string
    {
        $session = $this->booking->session;

        return ($session->translate($this->language()) ?? $session->translate('en'))?->title ?? '';
    }
}
