<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * One item in the programme: a talk, a conversation, a workshop.
 *
 * A session may be bookable, meaning it has its own sign-up form and a limited
 * number of places — the day registration does not cover it.
 */
class Session extends Model implements TranslatableContract
{
    use Translatable;

    protected $connection = 'wcm_2026';

    protected $fillable = [
        'programme_day_id', 'starts_at', 'ends_at', 'kind',
        'school', 'published', 'position', 'slug', 'bookable', 'capacity',
    ];

    protected $casts = [
        'school' => 'boolean',
        'published' => 'boolean',
        'bookable' => 'boolean',
        'capacity' => 'integer',
    ];

    public array $translatedAttributes = ['title', 'subtitle', 'audience', 'description'];

    public function day(): BelongsTo
    {
        return $this->belongsTo(ProgrammeDay::class, 'programme_day_id');
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot('position')
            ->orderBy('person_session.position');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SessionBooking::class);
    }

    /** Places taken. A cancelled booking frees its place. */
    public function taken(): int
    {
        return $this->bookings()->whereNull('cancelled_at')->count();
    }

    /** Null when the session is not capped — not the same as nought left. */
    public function getPlacesLeftAttribute(): ?int
    {
        return $this->capacity === null ? null : max(0, $this->capacity - $this->taken());
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->places_left < 1;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
