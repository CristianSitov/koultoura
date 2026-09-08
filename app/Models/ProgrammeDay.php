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
     * The Heritage School runs on the 7th, the 9th and the 10th only. The 8th
     * is given over to the symposium's own sessions, so a workshop cannot be
     * put there — see saveSession() in the backoffice, which enforces it.
     */
    public const SCHOOL_DAYS = [7, 9, 10];

    protected $fillable = ['date', 'theme_id', 'position', 'published'];

    protected $casts = [
        'date' => 'date',
        'published' => 'boolean',
    ];

    public array $translatedAttributes = ['name', 'description'];

    /** Whether a Heritage School workshop may be scheduled on this day. */
    public function hostsSchool(): bool
    {
        return in_array((int) $this->date->format('j'), self::SCHOOL_DAYS, true);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class)->orderBy('starts_at')->orderBy('position');
    }
}
