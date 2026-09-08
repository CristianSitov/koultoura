<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/*
 * The account that logs in.
 *
 * `User` is two things in this app: the handful of organisers with a password,
 * and the people who signed up for an edition — 2022 and 2024 both create and
 * read their own subscribers through it, on whichever yearly database the URL
 * selects. That works for a page under /2024, and not at all for /login, which
 * carries no year: SetYearlyDatabase falls back to the current edition, and
 * the current edition has no users table.
 *
 * So the guard gets its own model, pinned to the database the organiser
 * accounts actually live in. Everything else about it is `User`; only where to
 * look is fixed.
 *
 * And narrowed: that table is also the 2024 subscriber list, so a password hash
 * on any row in it was a working backoffice login. Only rows flagged
 * `backoffice` are accounts here — the scope is global, so it covers the
 * guard's own credential lookup and not just the queries written by hand.
 */
class Admin extends User
{
    protected $connection = 'wcm_2024';

    // Without this, Eloquent would go looking for an `admins` table.
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('backoffice', fn (Builder $query) => $query->where('backoffice', true));
    }
}
