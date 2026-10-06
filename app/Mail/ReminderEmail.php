<?php

namespace App\Mail;

use App\Models\ProgrammeDay;
use App\Models\Registration;
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
 * reads the English), with {name} in the text replaced by theirs and {days}
 * by the days they registered for. On a line of its own {days} is a list —
 * one day a line, even when there is only one; inside a sentence it reads
 * "Wednesday 7, Thursday 8 and Friday 9 October", and so does the subject.
 */
class ReminderEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @param  int[]  $days  the registration's day numbers (1 = 7 October) */
    public function __construct(public Reminder $reminder, public string $language, public string $name, public array $days = Registration::DAYS)
    {
    }

    /** The days as a list, one a line: "Wednesday, 7 October". */
    public static function daysList(array $days, string $locale): string
    {
        $items = self::dates($days, $locale)
            ->map(fn ($date) => '<li style="font-size:16px;line-height:1.5em;margin:0 0 4px;"><strong>'.e($date->translatedFormat('l, j F')).'</strong></li>')
            ->implode('');

        // Sized like the paragraphs: the mail theme sizes <p> only, and a
        // list left to the mail app's default came out noticeably smaller.
        return $items === '' ? '' : '<ul style="font-size:16px;line-height:1.5em;margin:0 0 16px;padding-left:24px;">'.$items.'</ul>';
    }

    private static function dates(array $days, string $locale)
    {
        $dates = ProgrammeDay::orderBy('position')->orderBy('date')->pluck('date')->values();

        return collect($days)
            ->map(fn ($n) => $dates->get((int) $n - 1))
            ->filter()
            ->unique(fn ($date) => $date->toDateString())
            ->sortBy(fn ($date) => $date->toDateString())
            ->map(fn ($date) => $date->copy()->locale($locale))
            ->values();
    }

    /** Day numbers as words, in a language: weekday and date, the month once. */
    public static function daysText(array $days, string $locale): string
    {
        $picked = self::dates($days, $locale);

        if ($picked->isEmpty()) {
            return '';
        }

        // "Wednesday 7, Thursday 8 and Friday 9 October": each month named
        // once, after its last day.
        $words = $picked->map(function ($date, $i) use ($picked) {
            $next = $picked->get($i + 1);
            $text = $date->translatedFormat('l j');

            return ($next && $next->month === $date->month) ? $text : $text.' '.$date->translatedFormat('F');
        });

        $and = $locale === 'ro' ? ' și ' : ' and ';

        return $words->count() === 1
            ? $words->first()
            : $words->slice(0, -1)->implode(', ').$and.$words->last();
    }

    public function envelope(): Envelope
    {
        $subject = str_replace(['{name}', '{days}'], [$this->name, self::daysText($this->days, $this->language)], $this->pick('subject'));

        return new Envelope(subject: $subject ?: 'Why Culture Matters 2026');
    }

    public function content(): Content
    {
        App::setLocale($this->language);

        return new Content(markdown: 'emails.reminder', with: [
            // The body is HTML, so the name goes in escaped.
            'body' => $this->body(),
        ]);
    }

    private function body(): string
    {
        $body = HtmlBio::clean($this->pick('body')) ?? '';

        // {days} alone on its line (bold or not) becomes the list.
        $body = preg_replace(
            '#<p>\s*(?:<strong>)?\s*\{days\}\s*(?:</strong>)?\s*</p>#u',
            self::daysList($this->days, $this->language),
            $body
        );

        return str_replace(
            ['{name}', '{days}'],
            [e($this->name), e(self::daysText($this->days, $this->language))],
            $body
        );
    }

    private function pick(string $field): string
    {
        $ro = $this->language === 'ro' ? $this->reminder->{$field.'_ro'} : null;

        return (string) (filled(strip_tags((string) $ro)) ? $ro : $this->reminder->{$field});
    }
}
