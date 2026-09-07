<?php

namespace App\Models;

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
 */
class Admin extends User
{
    protected $connection = 'wcm_2024';

    // Without this, Eloquent would go looking for an `admins` table.
    protected $table = 'users';
}
