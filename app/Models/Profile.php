<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @package App\Models
 * @mixin Model
 */
class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'phone',
        'job',
        'organization',
        'country',
        'event_details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function getEventDetailsAttribute($value)
    {
        return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
    }
}
