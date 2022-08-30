<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Person extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'people';

    public $fillable = [
        'full_name',
    ];

    public array $translatedAttributes = [
        'role',
        'institution',
        'description',
    ];
}
