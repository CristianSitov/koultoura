<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @package App\Models
 * @mixin Model
 */
class Day extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = [
        'name',
        'title',
        'description',
        'location',
    ];

    public function host(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'host_id');
    }
}
