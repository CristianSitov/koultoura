<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'location',
    ];
}
