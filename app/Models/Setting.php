<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A switch the office can throw, stored rather than deployed.
 *
 * Values are strings; `bool` reads the ones that are yes-or-no questions.
 */
class Setting extends Model
{
    protected $connection = 'wcm_2026';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /** Whether the programme section appears on the public page at all. */
    public const PROGRAMME_VISIBLE = 'programme_visible';

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::find($key)?->value;

        return $value === null ? $default : $value === '1';
    }

    public static function put(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
    }
}
