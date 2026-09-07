<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Model;

class SessionTranslation extends Model
{
    public $timestamps = false;

    protected $connection = 'wcm_2026';

    protected $fillable = ['title', 'subtitle', 'audience', 'description'];
}
