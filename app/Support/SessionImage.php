<?php

namespace App\Support;

/*
 * The picture on a workshop or a guided tour, stored the same way and at the
 * same size as a guest portrait — one image pipeline, two callers.
 */
class SessionImage
{
    public const DIR = 'assets/2026/sessions';

    public const MAX_EDGE = 1400;

    /** Writes {slug}.jpg and returns the public path; the slug is the session's own. */
    public static function store(string $slug, string $bytes): string
    {
        return ImageFile::store(self::DIR, $slug, $bytes, self::MAX_EDGE);
    }
}
