<?php

namespace App\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role',
        'institution',
        'description',
    ];
}
