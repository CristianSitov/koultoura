<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
 * Clears out built assets nothing points at any more.
 *
 * The build no longer empties public/build, because wiping the directory nginx
 * is serving breaks anyone who loaded a page just before a deploy: their
 * browser asks for a chunk that has been deleted and the page does not render.
 * Old files staying put is what fixes that — and what makes this necessary,
 * or the directory grows with every deploy forever.
 *
 * Two conditions before anything is deleted, and the second is the important
 * one: the file must be absent from the current manifest *and* older than the
 * grace period. A browser holding a page from an hour ago is still entitled to
 * the chunks that page names.
 */
class PruneBuildAssets extends Command
{
    protected $signature = 'build:prune
                            {--days=7 : Keep unreferenced files younger than this}
                            {--dry-run : List what would go without deleting it}';

    protected $description = 'Delete built assets no longer named by the manifest';

    public function handle(): int
    {
        $build = public_path('build');
        $manifest = $build.'/manifest.json';

        if (! is_file($manifest)) {
            $this->error('No manifest at '.$manifest.' — has the site been built?');

            return self::FAILURE;
        }

        $entries = json_decode(file_get_contents($manifest), true);

        if (! is_array($entries)) {
            $this->error('The manifest is not readable JSON. Refusing to delete anything.');

            return self::FAILURE;
        }

        // Every path the current build can ask for: the entry itself, its CSS,
        // and anything it imports.
        $keep = [];

        array_walk_recursive($entries, function ($value, $key) use (&$keep) {
            if (in_array($key, ['file', 'src'], true) || is_int($key)) {
                $keep[$value] = true;
            }
        });

        $cutoff = now()->subDays((int) $this->option('days'))->getTimestamp();
        $dryRun = $this->option('dry-run');
        $gone = 0;
        $freed = 0;
        $kept = 0;

        foreach (glob($build.'/assets/*') ?: [] as $path) {
            $relative = 'assets/'.basename($path);

            if (isset($keep[$relative])) {
                $kept++;

                continue;
            }

            if (filemtime($path) > $cutoff) {
                $kept++;

                continue;
            }

            $freed += filesize($path);
            $gone++;

            if ($dryRun) {
                $this->line('  would delete '.$relative);

                continue;
            }

            @unlink($path);
        }

        $this->line(sprintf(
            '%s %d file(s), %s freed. %d kept (in the manifest, or newer than %d days).',
            $dryRun ? 'Would delete' : 'Deleted',
            $gone,
            $this->size($freed),
            $kept,
            (int) $this->option('days')
        ));

        return self::SUCCESS;
    }

    private function size(int $bytes): string
    {
        return $bytes > 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024).' KB';
    }
}
