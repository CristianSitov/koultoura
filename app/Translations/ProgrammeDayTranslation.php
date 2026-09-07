<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Model;

class ProgrammeDayTranslation extends Model
{
    public $timestamps = false;

    protected $connection = 'wcm_2026';

    protected $fillable = ['name', 'description'];
}
