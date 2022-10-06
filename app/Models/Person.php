<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public array $translatedAttributes = [
        'role',
        'institution',
        'description',
    ];

    public function getSlugAttribute() {
        return Str::studly(Str::slug($this->full_name));
    }
}
