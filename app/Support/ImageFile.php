<?php

namespace App\Support;

/*
 * One place that takes uploaded image bytes, shrinks anything oversized, and
 * writes a JPEG. Portraits and workshop pictures arrive at camera resolution
 * and nobody is watching page weight, so this is the single knob that keeps
 * them sane. GuestPhoto and SessionImage both hand their bytes here.
 */
class ImageFile
{
    /** Writes {dir}/{name}.jpg under public/, shrinking to maxEdge, returns its public path. */
    public static function store(string $dir, string $name, string $bytes, int $maxEdge): string
    {
        $relative = trim($dir, '/')."/{$name}.jpg";
        $path = public_path($relative);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            // Not something GD reads; keep the original bytes rather than lose it.
            file_put_contents($path, $bytes);

            return '/'.$relative;
        }

        $edge = max(imagesx($image), imagesy($image));
        $scaled = $edge > $maxEdge
            ? imagescale($image, (int) round(imagesx($image) * $maxEdge / $edge))
            : $image;

        imagejpeg($scaled, $path, 82);
        imagedestroy($scaled);

        if ($scaled !== $image) {
            imagedestroy($image);
        }

        return '/'.$relative;
    }
}
