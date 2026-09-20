<?php

namespace App\Mail;

use App\Models\SessionPlace;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The invitation to hold a place in an internal workshop. */
class PlaceInvite extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public SessionPlace $place, public string $confirmUrl)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your place at Why Culture Matters 2026');
    }

    public function content(): Content
    {
        $session = $this->place->session;

        return new Content(markdown: 'emails.place-invite', with: [
            'title' => $session->translate('en')?->title ?? 'a workshop',
            'code' => $this->place->code,
            'confirmUrl' => $this->confirmUrl,
        ]);
    }
}
