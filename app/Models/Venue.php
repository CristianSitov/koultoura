<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venue extends Model
{
    use Translatable;

    public array $translatedAttributes = [
        'location',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }
}
