<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @package App\Models
 * @mixin Model
 */
class Presentation extends Model implements TranslatableContract
{
    use Translatable;

    protected $dates = [
        'starts_at',
        'ends_at',
        'created_at',
        'updated_at',
    ];

    public array $translatedAttributes = [
        'title',
        'supratitle',
        'subtitle',
        'description',
    ];

    public const SPEAKER = 'speaker';
    public const MODERATOR = 'moderator';
    public const FACILITATOR = 'facilitator';
    public const CHAIRPERSON = 'chairperson';
    public const REPORTER = 'reporter';
    public const URBAN_GUIDE = 'urban_guide';

    public function getStartsAtAttribute($value): string
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::SPEAKER)
            ->orderBy('person_presentation.order', 'ASC');
    }

    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::MODERATOR)
            ->orderBy('person_presentation.order', 'ASC');
    }

    public function facilitators(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::FACILITATOR)
            ->orderBy('person_presentation.order', 'ASC');
    }

    public function chairpersons(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::CHAIRPERSON)
            ->orderBy('person_presentation.order', 'ASC');
    }

    public function reporters(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::REPORTER)
            ->orderBy('person_presentation.order', 'ASC');
    }

    public function urban_guides(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::URBAN_GUIDE)
            ->orderBy('person_presentation.order', 'ASC');
    }
}
