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

    protected $fillable = ['date', 'theme_id', 'position', 'published'];

    protected $casts = [
        'date' => 'date',
        'published' => 'boolean',
    ];

    public array $translatedAttributes = ['name', 'description'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class)->orderBy('starts_at')->orderBy('position');
    }
}
