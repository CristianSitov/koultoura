<?php

namespace App\Mail;

use App\Http\Controllers\Front2026Controller;
use App\Models\SessionBooking;
use App\Support\HtmlBio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

/**
 * The reminder before a workshop or tour, to everyone holding a place in it —
 * confirmed or not. Which one, when, the day's brief and the workshop's own
 * description (where the office writes the meeting point), and a link to the
 * booking page. The date is written out, never "tomorrow": the office may
 * send it two days ahead.
 *
 * Sent from Workshops, one person per request. Always through `sendTo`, which
 * records it; a test or preview builds one from an unsaved booking.
 */
class SessionReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    // Where a change of plan goes: the emails come from a no-reply address.
    public const CONTACT = 'contact@prinbanat.ro';

    public function __construct(public SessionBooking $booking)
    {
    }

    public static function sendTo(SessionBooking $booking): void
    {
        Mail::to($booking->email)->send(new self($booking->loadMissing('session.translations', 'session.day.translations')));

        $booking->forceFill(['reminder_sent_at' => now()])->save();
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->language());

        return new Envelope(subject: __('booking.reminder.subject', ['title' => $this->title(), 'date' => $this->date()]));
    }

    public function content(): Content
    {
        $locale = $this->language();
        App::setLocale($locale);

        $session = $this->booking->session;
        // Romanian left empty reads the English.
        $text = fn ($model) => HtmlBio::clean($model?->translate($locale)?->description)
            ?? HtmlBio::clean($model?->translate('en')?->description);

        return new Content(markdown: 'emails.session-reminder', with: [
            'name' => $this->booking->first_name ?: $this->booking->name,
            'title' => $this->title(),
            'date' => $this->date(),
            'time' => substr($session->starts_at, 0, 5),
            'tour' => $session->type === 'tour',
            'brief' => $text($session->day),
            'description' => $text($session),
            'contact' => self::CONTACT,
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

    private function date(): string
    {
        return $this->booking->session->day?->date->locale($this->language())->translatedFormat('l, j F') ?? '';
    }
}
