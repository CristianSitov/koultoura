<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Something on the internal agenda that is not in the public programme: a
 * meeting, a meal, a round table, an outing — arranged for speakers, guests
 * and the team.
 *
 * It carries its own date rather than hanging off a programme day: the agenda
 * also covers the day before the symposium and the day after, when guests
 * arrive and leave, and those are not programme days.
 */
class AgendaEvent extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'date', 'starts_at', 'ends_at',
        'title', 'title_ro', 'location', 'location_ro', 'description', 'description_ro',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** A text in the language asked for; Romanian left empty reads as English. */
    public function text(string $field, string $locale): ?string
    {
        $translated = $locale === 'ro' ? $this->{$field.'_ro'} : null;

        return filled($translated) ? $translated : $this->{$field};
    }
}
