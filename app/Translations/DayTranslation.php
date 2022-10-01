<?php

namespace App\Translations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description',
        'location',
    ];

    public function getNameAttribute($value): string
    {
        return Carbon::parse($value)->translatedFormat(app()->getLocale() === 'ro' ? 'd F' : 'F d');
    }
}
