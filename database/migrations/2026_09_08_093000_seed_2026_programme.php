<?php

use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

/*
 * Moves the programme that lived in resources/js/Sections/2026/programme.js
 * into the database, so it can be edited without a deploy.
 *
 * It is still the placeholder schedule — the guests are real, what they are
 * down to speak about is not — and the page still says "Draft". The point of
 * seeding it rather than starting empty is that the section keeps working, and
 * the office has rows to edit instead of a blank screen.
 *
 * Romanian comes out of lang/ro.json, where these strings were translated when
 * the titles doubled as translation keys.
 */
return new class extends Migration
{
    private array $ro = [];

    public function up(): void
    {
        $this->ro = json_decode(file_get_contents(base_path('lang/ro.json')), true) ?: [];

        if (Theme::count() > 0) {
            return;
        }

        $themes = [];

        foreach ([1 => 'I', 2 => 'II', 3 => 'III'] as $n => $numeral) {
            $themes[$numeral] = Theme::create([
                'numeral' => $numeral,
                'position' => $n,
                'en' => ['title' => $this->en("theme.$n.title"), 'description' => $this->en("theme.$n.body")],
                'ro' => ['title' => $this->ro("theme.$n.title"), 'description' => $this->ro("theme.$n.body")],
            ]);
        }

        foreach ($this->days() as $i => $day) {
            $row = ProgrammeDay::create([
                'date' => $day['date'],
                'theme_id' => $themes[$day['theme']]->id,
                'position' => $i + 1,
                'published' => true,
                'en' => ['name' => ucfirst($day['key'])],
                'ro' => ['name' => $this->ro('weekday.'.$day['key'])],
            ]);

            foreach ($day['sessions'] as $j => $session) {
                $this->session($row, $session, $j + 1);
            }
        }
    }

    private function session(ProgrammeDay $day, array $data, int $position): void
    {
        // `who` is either people who are speaking or the audience it is for.
        $speakers = $this->speakers($data['who']);

        $row = Session::create([
            'programme_day_id' => $day->id,
            'starts_at' => $data['time'].':00',
            'kind' => $data['kind'],
            'school' => $data['school'] ?? false,
            'published' => true,
            'position' => $position,
            'en' => [
                'title' => $data['title'],
                'audience' => $speakers->isEmpty() ? $data['who'] : null,
            ],
            'ro' => [
                'title' => $this->ro($data['title']),
                'audience' => $speakers->isEmpty() ? $this->ro($data['who']) : null,
            ],
        ]);

        foreach ($speakers as $k => $person) {
            $row->speakers()->attach($person->id, ['position' => $k + 1]);
        }
    }

    /**
     * Names in `who` that match a guest; an audience label matches nothing.
     *
     * Explicitly on the 2026 connection: Person follows whatever the default
     * is, and under `artisan migrate` that is the 2022 database — where some
     * of these people also spoke, under different ids.
     */
    private function speakers(string $who)
    {
        return collect(explode(',', $who))
            ->map(fn ($name) => Person::on('wcm_2026')->where('full_name', trim($name))->first())
            ->filter()
            ->values();
    }

    private function en(string $key): string
    {
        static $en;
        $en ??= json_decode(file_get_contents(base_path('lang/en.json')), true) ?: [];

        return $en[$key] ?? $key;
    }

    private function ro(string $key): string
    {
        return $this->ro[$key] ?? $key;
    }

    private function days(): array
    {
        return [
            ['key' => 'wednesday', 'date' => '2026-10-07', 'theme' => 'I', 'sessions' => [
                ['time' => '10:00', 'kind' => '', 'title' => 'Opening', 'who' => 'Asociația Prin Banat'],
                ['time' => '10:30', 'kind' => 'Talk', 'title' => 'Evacuating a collection under fire', 'who' => 'Oleksandra Kovalchuk'],
                ['time' => '11:15', 'kind' => 'Talk', 'title' => 'After the factory: industrial heritage at risk', 'who' => 'Raluca-Maria Trifa'],
                ['time' => '12:00', 'kind' => 'Conversation', 'title' => 'What we lose first', 'who' => 'Gabriela Robeci'],
                ['time' => '16:00', 'kind' => 'Talk', 'title' => 'Sensing what is left', 'who' => 'Anđela Petrović'],
            ]],
            ['key' => 'thursday', 'date' => '2026-10-08', 'theme' => 'II', 'sessions' => [
                ['time' => '10:00', 'kind' => 'Talk', 'title' => 'Budapest100: research as public invitation', 'who' => 'Barbara Szij'],
                ['time' => '10:45', 'kind' => 'Talk', 'title' => 'Twenty years of museum education', 'who' => 'Iulia Iordan'],
                ['time' => '11:30', 'kind' => 'Conversation', 'title' => 'Learning through place', 'who' => 'Andreea Lazea'],
                ['time' => '14:00', 'kind' => 'Workshop 1', 'title' => 'Reading a facade', 'who' => 'Children, 8–12', 'school' => true],
                ['time' => '16:00', 'kind' => 'Workshop 2', 'title' => 'Mapping your street', 'who' => 'Young people', 'school' => true],
            ]],
            ['key' => 'friday', 'date' => '2026-10-09', 'theme' => 'III', 'sessions' => [
                ['time' => '10:00', 'kind' => 'Talk', 'title' => 'Funeral rites and the stories communities keep', 'who' => 'Nicoleta Mușat'],
                ['time' => '10:45', 'kind' => 'Talk', 'title' => 'Late modern, badly loved', 'who' => 'András Mudra'],
                ['time' => '11:30', 'kind' => 'Conversation', 'title' => 'Whose story is it', 'who' => 'Gabriela Robeci'],
                ['time' => '14:00', 'kind' => 'Workshop 3', 'title' => "Collecting a neighbourhood's voices", 'who' => 'Adults', 'school' => true],
                ['time' => '16:00', 'kind' => 'Workshop 4', 'title' => 'Objects that carry a family', 'who' => 'All ages', 'school' => true],
            ]],
            ['key' => 'saturday', 'date' => '2026-10-10', 'theme' => 'III', 'sessions' => [
                ['time' => '10:00', 'kind' => 'Workshop 5', 'title' => 'Drawing the invisible city', 'who' => 'Children, 8–12', 'school' => true],
                ['time' => '11:30', 'kind' => 'Workshop 6', 'title' => 'A walk that asks questions', 'who' => 'Young people', 'school' => true],
                ['time' => '14:00', 'kind' => 'Workshop 7', 'title' => 'Teaching heritage without a textbook', 'who' => 'Educators', 'school' => true],
                ['time' => '16:00', 'kind' => 'Workshop 8', 'title' => 'What we pass on', 'who' => 'All ages', 'school' => true],
                ['time' => '17:30', 'kind' => 'Closing', 'title' => 'What we choose to protect', 'who' => 'Andreea Lazea, Gabriela Robeci'],
            ]],
        ];
    }

    public function down(): void
    {
        Session::query()->delete();
        ProgrammeDay::query()->delete();
        Theme::query()->delete();
    }
};
