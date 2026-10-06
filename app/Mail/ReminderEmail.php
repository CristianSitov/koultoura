<?php

namespace App\Mail;

use App\Models\Reminder;
use App\Support\HtmlBio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * The reminder as one person gets it: in their language (Romanian left empty
 * reads the English), with {name} in the text replaced by theirs.
 */
class ReminderEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Reminder $reminder, public string $language, public string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: str_replace('{name}', $this->name, $this->pick('subject')) ?: 'Why Culture Matters 2026');
    }

    public function content(): Content
    {
        App::setLocale($this->language);

        return new Content(markdown: 'emails.reminder', with: [
            // The body is HTML, so the name goes in escaped.
            'body' => str_replace('{name}', e($this->name), HtmlBio::clean($this->pick('body')) ?? ''),
        ]);
    }

    private function pick(string $field): string
    {
        $ro = $this->language === 'ro' ? $this->reminder->{$field.'_ro'} : null;

        return (string) (filled(strip_tags((string) $ro)) ? $ro : $this->reminder->{$field});
    }
}
