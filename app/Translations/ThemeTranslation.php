<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Model;

class ThemeTranslation extends Model
{
    public $timestamps = false;

    protected $connection = 'wcm_2026';

    protected $fillable = ['title', 'description'];
}
