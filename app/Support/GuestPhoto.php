<?php

namespace App\Support;

/*
 * Where a guest's portrait goes, and what shape it arrives in.
 *
 * Shared by the Drive sync and the backoffice upload, because a photo added by
 * hand should not be a different size or live somewhere else than one imported.
 */
class GuestPhoto
{
    public const DIR = 'assets/2026/guests';

    /** Portraits arrive at camera resolution; nobody is watching page weight. */
    public const MAX_EDGE = 1000;

    /**
     * Writes the bytes as {slug}.jpg and returns the public path.
     *
     * The name is the slug, so it stays stable across re-uploads — the profile
     * URL derives from the same slug, and cache-busting is the browser's problem.
     */
    public static function store(string $slug, string $bytes): string
    {
        return ImageFile::store(self::DIR, $slug, $bytes, self::MAX_EDGE);
    }
}
