<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresentationTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
    ];
}
