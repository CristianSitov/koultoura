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
 * by the days they registered for — "Wednesday 7, Thursday 8 and Friday 9
 * October" / "miercuri 7, joi 8 și vineri 9 octombrie".
 */
class ReminderEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @param  int[]  $days  the registration's day numbers (1 = 7 October) */
    public function __construct(public Reminder $reminder, public string $language, public string $name, public array $days = Registration::DAYS)
    {
    }

    /** Day numbers as words, in a language: weekday and date, the month once. */
    public static function daysText(array $days, string $locale): string
    {
        $dates = ProgrammeDay::orderBy('position')->orderBy('date')->pluck('date')->values();

        $picked = collect($days)
            ->map(fn ($n) => $dates->get((int) $n - 1))
            ->filter()
            ->unique(fn ($date) => $date->toDateString())
            ->sortBy(fn ($date) => $date->toDateString())
            ->map(fn ($date) => $date->copy()->locale($locale))
            ->values();

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
            'body' => str_replace(
                ['{name}', '{days}'],
                [e($this->name), e(self::daysText($this->days, $this->language))],
                HtmlBio::clean($this->pick('body')) ?? ''
            ),
        ]);
    }

    private function pick(string $field): string
    {
        $ro = $this->language === 'ro' ? $this->reminder->{$field.'_ro'} : null;

        return (string) (filled(strip_tags((string) $ro)) ? $ro : $this->reminder->{$field});
    }
}
