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

/**
 * A workshop or tour place, in writing: which one, when, and the link back to
 * the booking. Booking itself sends nothing — the page says the place is held
 * — so this goes out by hand from Workshops, e.g. after the office corrects an
 * address that was mistyped.
 */
class BookingConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public SessionBooking $booking)
    {
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
