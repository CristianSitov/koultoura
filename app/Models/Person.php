<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Person extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'people';

    public $appends = [
        'slug',
    ];

    public $fillable = [
        'full_name',
        'slug',
        'avatar',
        'institution_url',
        'position',
        'drive_folder_id',
        'drive_photo_id',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public array $translatedAttributes = [
        'role',
        'institution',
        'description',
    ];

    /**
     * 2026 stores the slug, because it is the profile's URL and has to survive
     * a correction to the name. Earlier years have no such column and keep
     * deriving it.
     */
    public function getSlugAttribute($value) {
        return $value ?: Str::studly(Str::slug($this->full_name));
    }

    /**
     * What this person is down to speak at. 2026 only: earlier editions have
     * no sessions table, and nothing on their pages asks for this.
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class)->withPivot('position');
    }
}
