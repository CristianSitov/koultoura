<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A reminder email to everyone registered — see Admin\ReminderController. */
class Reminder extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = ['subject', 'subject_ro', 'body', 'body_ro'];

    public function sends(): HasMany
    {
        return $this->hasMany(ReminderSend::class);
    }

    /** The one being worked on: the latest, or a fresh one if there is none. */
    public static function current(): self
    {
        return static::latest('id')->first() ?? static::create(['subject' => '']);
    }
}
