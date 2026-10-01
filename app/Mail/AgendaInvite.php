<?php

namespace App\Mail;

use App\Models\AgendaRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/*
 * The link to the internal agenda, in the recipient's language. Only a link:
 * the agenda keeps changing until the day, and a copy in an inbox would be
 * out of date the moment the office moves a lunch.
 */
class AgendaInvite extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public AgendaRecipient $recipient, public string $url)
    {
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->recipient->locale);

        return new Envelope(subject: __('Your programme · Why Culture Matters 2026'));
    }

    public function content(): Content
    {
        App::setLocale($this->recipient->locale);

        return new Content(markdown: 'emails.agenda-invite', with: [
            'name' => $this->recipient->name,
            'url' => $this->url,
        ]);
    }
}
