<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
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
        'subtitle',
        'description',
    ];

    public const SPEAKER = 'speaker';
    public const MODERATOR = 'moderator';

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::SPEAKER);
    }

    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'person_presentation',
            'presentation_id',
            'person_id'
        )
            ->where('person_presentation.person_type', '=', self::MODERATOR);
    }
}
