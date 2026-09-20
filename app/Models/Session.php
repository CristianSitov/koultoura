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
        'programme_day_id', 'starts_at', 'ends_at', 'kind', 'type', 'image',
        'school', 'youth', 'published', 'position', 'slug', 'bookable', 'capacity', 'internal',
    ];

    /** The two exceptions to a plain slot — clickable, bookable, with a panel. */
    public const EXCEPTIONS = ['workshop', 'tour'];

    protected $casts = [
        'school' => 'boolean',
        'youth' => 'boolean',
        'published' => 'boolean',
        'bookable' => 'boolean',
        'internal' => 'boolean',
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

    public function places(): HasMany
    {
        return $this->hasMany(SessionPlace::class)->orderBy('id');
    }

    /*
     * An internal workshop hands out one place per seat. Bring the count of
     * places in line with the capacity: make the missing ones (each gets its
     * own code), and drop only the still-empty extras if the capacity shrinks —
     * a place someone was invited to is never silently removed.
     */
    public function syncPlaces(): void
    {
        $target = (int) ($this->capacity ?? 0);
        $current = $this->places()->count();

        if ($current < $target) {
            foreach (range($current + 1, $target) as $ignored) {
                $this->places()->create([]);
            }
        } elseif ($current > $target) {
            $remove = $this->places()->where('status', 'open')->whereNull('email')
                ->orderByDesc('id')
                ->take($current - $target)
                ->pluck('id');

            $this->places()->whereIn('id', $remove)->delete();
        }
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

    /** A workshop or a tour: the kind with a details panel and its own form. */
    public function isException(): bool
    {
        return in_array($this->type, self::EXCEPTIONS, true);
    }
}
