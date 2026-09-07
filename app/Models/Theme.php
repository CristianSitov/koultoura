<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * One of the three strands of the 2026 edition. A theme can cover more than one
 * day, which is why the days point at it rather than the reverse.
 */
class Theme extends Model implements TranslatableContract
{
    use Translatable;

    protected $connection = 'wcm_2026';

    protected $fillable = ['numeral', 'position'];

    public array $translatedAttributes = ['title', 'description'];

    public function days(): HasMany
    {
        return $this->hasMany(ProgrammeDay::class)->orderBy('position');
    }
}
