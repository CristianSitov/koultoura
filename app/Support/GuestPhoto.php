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

    /** Writes the bytes as {slug}.jpg and returns the public path. */
    public static function store(string $slug, string $bytes): string
    {
        $path = public_path(self::DIR."/{$slug}.jpg");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            // Not something GD reads; keep the original bytes rather than lose it.
            file_put_contents($path, $bytes);

            return '/'.self::DIR."/{$slug}.jpg";
        }

        $edge = max(imagesx($image), imagesy($image));
        $scaled = $edge > self::MAX_EDGE
            ? imagescale($image, (int) round(imagesx($image) * self::MAX_EDGE / $edge))
            : $image;

        imagejpeg($scaled, $path, 82);
        imagedestroy($scaled);

        if ($scaled !== $image) {
            imagedestroy($image);
        }

        // Cache-busting is the browser's problem; the name has to stay stable
        // because it is derived from the slug the profile URL uses.
        return '/'.self::DIR."/{$slug}.jpg";
    }
}
