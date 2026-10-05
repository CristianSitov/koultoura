<?php

namespace App\Mail;

use App\Models\SessionPlace;
use App\Support\HtmlBio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once a place is confirmed: when it is, the place code, what the
 * workshop is about, and the calendar links. Sent again from Workshops
 * ("Resend confirmation") for a place already confirmed.
 */
class PlaceConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public SessionPlace $place,
        public string $icsUrl,
        public string $googleUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your place is confirmed · Why Culture Matters 2026');
    }

    public function content(): Content
    {
        $session = $this->place->session->loadMissing('day', 'translations');

        return new Content(markdown: 'emails.place-confirmed', with: [
            'title' => $session->translate('en')?->title ?? 'the workshop',
            'when' => $session->day->date->format('l, j F Y').' · '.substr($session->starts_at, 0, 5),
            // The formatted description, cleaned to the safe subset.
            'description' => HtmlBio::clean($session->translate('en')?->description),
            'code' => $this->place->code,
            'icsUrl' => $this->icsUrl,
            'googleUrl' => $this->googleUrl,
        ]);
    }
}
