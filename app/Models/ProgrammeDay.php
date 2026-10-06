<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * A day of the programme. Not `Day`: that model belongs to 2022 and 2024 and
 * reads a differently shaped table on their databases.
 */
class ProgrammeDay extends Model implements TranslatableContract
{
    use Translatable;

    protected $connection = 'wcm_2026';

    /*
     * The Heritage School runs on the 7th, the 8th and the 10th only. The 9th
     * is given over to the symposium's own sessions, so a workshop cannot be
     * put there — see saveSession() in the backoffice, which enforces it.
     */
    public const SCHOOL_DAYS = [7, 8, 10];

    protected $fillable = [
        'date', 'theme_id', 'moderator_id', 'position', 'published',
        // Its box on the internal agenda — see App\Support\Agenda.
        'agenda_starts_at', 'agenda_ends_at', 'agenda_note', 'agenda_note_ro',
        // The email the evening before — see App\Mail\DayBrief.
        'email_subject', 'email_subject_ro', 'email_body', 'email_body_ro',
    ];

    protected $casts = [
        'date' => 'date',
        'published' => 'boolean',
    ];

    public array $translatedAttributes = ['name', 'description'];

    /**
     * The day-before email's words: what the office saved, or — until it
     * writes in it — a starter in the voice of the other emails, with the day's
     * number in it and "About this day" if there is one. {name} and {date} are
     * filled in per person.
     *
     * @return array{subject: string, subject_ro: ?string, body: string, body_ro: ?string}
     */
    public function emailText(): array
    {
        if (filled($this->email_subject) || filled(strip_tags((string) $this->email_body))) {
            return [
                'subject' => (string) $this->email_subject,
                'subject_ro' => $this->email_subject_ro,
                'body' => (string) $this->email_body,
                'body_ro' => $this->email_body_ro,
            ];
        }

        $n = \App\Mail\DayBrief::dayNumber($this);
        $brief = fn (string $locale) => \App\Support\HtmlBio::clean($this->translate($locale)?->description) ?? '';

        return [
            'subject' => 'Tomorrow at Why Culture Matters 2026 · {date}',
            'subject_ro' => 'Mâine la Why Culture Matters 2026 · {date}',
            'body' => '<p>Dear {name},</p>'
                .'<p>Tomorrow, <strong>{date}</strong>, is day '.$n.' of <strong>Why Culture Matters 2026</strong>.</p>'
                .$brief('en')
                .'<p>You will find the day’s programme at the link below.</p>',
            'body_ro' => '<p>Dragă {name},</p>'
                .'<p>Mâine, <strong>{date}</strong>, este ziua '.$n.' a simpozionului <strong>Why Culture Matters 2026</strong>.</p>'
                .($brief('ro') ?: $brief('en'))
                .'<p>Programul zilei îl găsești la linkul de mai jos.</p>',
        ];
    }

    /** Whether a Heritage School workshop may be scheduled on this day. */
    public function hostsSchool(): bool
    {
        return in_array((int) $this->date->format('j'), self::SCHOOL_DAYS, true);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'moderator_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class)->orderBy('starts_at')->orderBy('position');
    }
}
