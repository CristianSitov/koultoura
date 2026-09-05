<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/*
 * Pulls the 2026 guests out of the organiser's Drive folder into the wcm_2026
 * people tables.
 *
 * The folder is the source of truth for WHO is a guest and for their photo.
 * It is not the source of truth for the copy on the page: the bio documents in
 * there are raw submissions, some of them in Romanian, and the text on the site
 * has been edited. So a description is written only when the row has none yet.
 * Pass --force when you really do want Drive to win.
 *
 * Only numbered folders are imported (01., 02., …); anything unnumbered is
 * still under discussion and is skipped.
 */
class SyncGuestsFromDrive extends Command
{
    protected $signature = 'guests:sync
                            {--dry-run : Report what would change without writing anything}
                            {--force : Let Drive overwrite descriptions that have already been edited}';

    protected $description = 'Import the 2026 guests (name, description, photo) from Google Drive';

    private const API = 'https://www.googleapis.com/drive/v3';

    private const PHOTO_DIR = 'assets/2026/guests';

    private const MAX_PHOTO_EDGE = 1000;

    public function handle(): int
    {
        $key = config('services.google_drive.key');
        $folder = config('services.google_drive.guests_folder');

        if (! $key || ! $folder) {
            $this->error('Set GOOGLE_DRIVE_API_KEY and GOOGLE_DRIVE_GUESTS_FOLDER first.');

            return self::FAILURE;
        }

        $dry = $this->option('dry-run');

        $folders = $this->children($folder, $key)
            ->filter(fn (array $f) => $f['mimeType'] === 'application/vnd.google-apps.folder')
            ->map(fn (array $f) => $f + ['parsed' => $this->parseFolderName($f['name'])])
            ->filter(fn (array $f) => $f['parsed'] !== null)
            ->sortBy(fn (array $f) => $f['parsed']['position'])
            ->values();

        if ($folders->isEmpty()) {
            $this->warn('No numbered guest folders found.');

            return self::SUCCESS;
        }

        foreach ($folders as $folder) {
            $this->importOne($folder, $key, $dry);
        }

        $this->newLine();
        $this->info(($dry ? 'Dry run: ' : '').$folders->count().' guest folder(s) processed.');

        return self::SUCCESS;
    }

    /**
     * "03. Barbara Szij (KEK HU) - speaker & workshop" becomes position 3, the
     * name, and the role. The parenthetical is an internal shorthand for the
     * institution, not something to publish, so it is deliberately ignored.
     */
    public function parseFolderName(string $title): ?array
    {
        if (! preg_match('/^\s*(\d+)\.\s*(.+)$/u', $title, $m)) {
            return null;
        }

        $rest = trim($m[2]);
        $role = '';

        if (preg_match('/^(.*?)\s+[-–—]\s+(.+)$/u', $rest, $split)) {
            $rest = trim($split[1]);
            $role = Str::ucfirst(trim($split[2]));
        }

        $name = trim(preg_replace('/\s*\([^)]*\)\s*/u', ' ', $rest));

        return [
            'position' => (int) $m[1],
            'name' => $name,
            'role' => $role,
        ];
    }

    private function importOne(array $folder, string $key, bool $dry): void
    {
        ['position' => $position, 'name' => $name, 'role' => $role] = $folder['parsed'];

        $slug = Str::slug($name);
        $files = $this->children($folder['id'], $key);
        $photo = $files->first(fn (array $f) => Str::startsWith($f['mimeType'], 'image/'));
        $doc = $files->first(fn (array $f) => $this->isDocument($f['mimeType']));

        $person = Person::firstOrNew(['drive_folder_id' => $folder['id']]);
        $changes = [];

        if ($person->full_name !== $name) {
            $changes[] = $person->exists ? "name: {$person->full_name} -> {$name}" : "new: {$name}";
        }

        $person->full_name = $name;
        $person->position = $position;

        // The slug is the profile's public URL, so it is only ever set once.
        if (! $person->slug) {
            $person->slug = $slug;
        }

        if ($photo && $person->drive_photo_id !== $photo['id']) {
            $changes[] = 'photo: '.$photo['name'];

            if (! $dry) {
                $person->avatar = $this->downloadPhoto($photo, $person->slug, $key);
                $person->drive_photo_id = $photo['id'];
            }
        }

        $translation = $person->translateOrNew('en');

        if ($role !== '' && ($translation->role === '' || $translation->role === null || $this->option('force'))) {
            $translation->role = $role;
        }

        $hasBio = filled($translation->description);

        if ($doc && (! $hasBio || $this->option('force'))) {
            $text = $dry ? null : $this->documentText($doc, $key);
            $changes[] = 'description from '.$doc['name'].($hasBio ? ' (overwriting)' : '');

            if (filled($text)) {
                $translation->description = $text;
            }
        } elseif ($doc && $hasBio) {
            $this->line("  <fg=gray>{$name}: kept the edited description ({$doc['name']} left alone)</>");
        }

        if ($changes === []) {
            $this->line("  <fg=gray>{$name}: unchanged</>");

            return;
        }

        $this->line('  '.($dry ? '[dry] ' : '')."{$name}: ".implode('; ', $changes));

        if ($dry) {
            return;
        }

        $person->synced_at = now();
        $person->save();
    }

    private function isDocument(string $mime): bool
    {
        return in_array($mime, [
            'application/vnd.google-apps.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ], true);
    }

    private function children(string $parent, string $key)
    {
        $response = Http::get(self::API.'/files', [
            'q' => "'{$parent}' in parents and trashed = false",
            'fields' => 'files(id,name,mimeType)',
            'pageSize' => 200,
            'key' => $key,
        ])->throw();

        return collect($response->json('files', []));
    }

    /**
     * A Google Doc has to be exported; a .docx is a zip whose document.xml
     * carries the text. Either way what comes back is plain paragraphs.
     */
    private function documentText(array $doc, string $key): string
    {
        if ($doc['mimeType'] === 'application/vnd.google-apps.document') {
            $text = Http::get(self::API."/files/{$doc['id']}/export", [
                'mimeType' => 'text/plain',
                'key' => $key,
            ])->throw()->body();

            return $this->tidy($text);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'guest');
        file_put_contents($tmp, $this->download($doc['id'], $key));

        $zip = new \ZipArchive();
        $xml = $zip->open($tmp) === true ? $zip->getFromName('word/document.xml') : '';
        $zip->close();
        unlink($tmp);

        $xml = str_replace(['</w:p>', '<w:br/>'], "\n", (string) $xml);

        return $this->tidy(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function tidy(string $text): string
    {
        $lines = array_filter(array_map('trim', preg_split('/\R+/u', $text)), 'strlen');

        return trim(implode("\n\n", $lines));
    }

    private function download(string $id, string $key): string
    {
        return Http::get(self::API."/files/{$id}", ['alt' => 'media', 'key' => $key])->throw()->body();
    }

    /**
     * Portraits come off Drive at full camera resolution, so they are scaled
     * down on the way in — this runs unattended and nobody is watching the
     * page weight.
     */
    private function downloadPhoto(array $photo, string $slug, string $key): string
    {
        $bytes = $this->download($photo['id'], $key);
        $image = @imagecreatefromstring($bytes);
        $path = public_path(self::PHOTO_DIR."/{$slug}.jpg");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if ($image === false) {
            // Not something GD reads; keep the original bytes rather than lose it.
            file_put_contents($path, $bytes);
        } else {
            $edge = max(imagesx($image), imagesy($image));
            $scaled = $edge > self::MAX_PHOTO_EDGE
                ? imagescale($image, (int) round(imagesx($image) * self::MAX_PHOTO_EDGE / $edge))
                : $image;

            imagejpeg($scaled, $path, 82);
            imagedestroy($scaled);

            if ($scaled !== $image) {
                imagedestroy($image);
            }
        }

        return '/'.self::PHOTO_DIR."/{$slug}.jpg";
    }
}
