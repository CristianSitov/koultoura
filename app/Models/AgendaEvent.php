<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 * Something on the internal agenda that is not in the public programme: a
 * meeting, a meal, a round table, an outing — arranged for speakers, guests
 * and the team.
 */
class AgendaEvent extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'programme_day_id', 'starts_at', 'ends_at',
        'title', 'title_ro', 'location', 'location_ro', 'description', 'description_ro',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(ProgrammeDay::class, 'programme_day_id');
    }

    /** A text in the language asked for; Romanian left empty reads as English. */
    public function text(string $field, string $locale): ?string
    {
        $translated = $locale === 'ro' ? $this->{$field.'_ro'} : null;

        return filled($translated) ? $translated : $this->{$field};
    }
}
