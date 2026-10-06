<?php

namespace App\Mail;

use App\Http\Controllers\Front2026Controller;
use App\Models\ProgrammeDay;
use App\Support\HtmlBio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * The evening before a day of the symposium: the office's own words for it —
 * subject and text, written in the backoffice like the reminder (Romanian
 * left empty reads the English), {name} and {date} filled in — then a button
 * to the day's own page.
 *
 * Sent from the backoffice, one person per request — see DayBriefController.
 */
class DayBrief extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ProgrammeDay $day, public string $language, public ?string $name = null)
    {
    }

    /** The day's place in the symposium: Day 1 is the first in programme order. */
    public static function dayNumber(ProgrammeDay $day): int
    {
        return ProgrammeDay::orderBy('position')->orderBy('date')->pluck('id')->search($day->id) + 1;
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->language);

        $subject = str_replace(['{name}', '{date}'], [(string) $this->name, $this->date()], $this->pick('subject'));

        return new Envelope(subject: $subject ?: 'Why Culture Matters 2026');
    }

    public function content(): Content
    {
        App::setLocale($this->language);

        // The body is HTML: the name goes in escaped.
        $body = str_replace(
            ['{name}', '{date}'],
            [e((string) $this->name), e($this->date())],
            HtmlBio::clean($this->pick('body')) ?? ''
        );

        // Romanian reads its own day page, at its own address.
        $url = url(Front2026Controller::base()
            .'/'.$this->language
            .'/'.Front2026Controller::sectionSlug('programme', $this->language)
            .'/'.Front2026Controller::daySlug($this->day->date));

        return new Content(markdown: 'emails.day-brief', with: ['body' => $body, 'url' => $url]);
    }

    /** Subject or body in the reader's language; Romanian left empty reads the English. */
    private function pick(string $field): string
    {
        $text = $this->day->emailText();
        $ro = $text[$field.'_ro'] ?? null;

        return (string) ($this->language === 'ro' && filled(strip_tags((string) $ro)) ? $ro : $text[$field]);
    }

    private function date(): string
    {
        return $this->day->date->locale($this->language)->translatedFormat('l, j F');
    }
}
