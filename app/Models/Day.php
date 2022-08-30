<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

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
        'location',
    ];
}
