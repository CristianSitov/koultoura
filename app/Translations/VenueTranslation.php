<?php

namespace App\Translations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'location',
    ];
}
