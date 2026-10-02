<?php

namespace App\Mail;

use App\Http\Controllers\Front2026Controller;
use App\Models\ProgrammeDay;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * The evening before a day of the symposium: a few lines about tomorrow — the
 * day's brief, written in the backoffice — and a link to the day's own page.
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

        return new Envelope(subject: __('day.email.subject', ['date' => $this->date()]));
    }

    public function content(): Content
    {
        App::setLocale($this->language);

        // A Romanian brief left empty reads the English one.
        $brief = \App\Support\HtmlBio::clean($this->day->translate($this->language)?->description)
            ?? \App\Support\HtmlBio::clean($this->day->translate('en')?->description);

        // Romanian reads its own day page, at its own address.
        $url = url(Front2026Controller::base()
            .'/'.$this->language
            .'/'.Front2026Controller::sectionSlug('programme', $this->language)
            .'/'.Front2026Controller::daySlug($this->day->date));

        return new Content(markdown: 'emails.day-brief', with: [
            'n' => self::dayNumber($this->day),
            'name' => $this->name,
            'date' => $this->date(),
            'theme' => $this->day->theme ? $this->day->theme->numeral.' · '.(($this->day->theme->translate($this->language) ?? $this->day->theme->translate('en'))?->title) : null,
            'brief' => $brief,
            'url' => $url,
        ]);
    }

    private function date(): string
    {
        return $this->day->date->locale($this->language)->translatedFormat('l, j F');
    }
}
