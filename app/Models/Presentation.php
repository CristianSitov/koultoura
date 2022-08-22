<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @package App\Models
 * @mixin Model
 */
class Presentation extends Model
{
    use HasFactory;

    protected $dates = [
        'starts_at',
        'ends_at',
        'created_at',
        'updated_at',
    ];

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }
}
