<?php

namespace App\Mail;

use App\Models\SessionPlace;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent once a place is confirmed — carries the calendar links. */
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
        $session = $this->place->session;

        return new Content(markdown: 'emails.place-confirmed', with: [
            'title' => $session->translate('en')?->title ?? 'the workshop',
            'code' => $this->place->code,
            'icsUrl' => $this->icsUrl,
            'googleUrl' => $this->googleUrl,
        ]);
    }
}
