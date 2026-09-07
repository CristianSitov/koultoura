<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Models\Person;
use App\Support\GuestPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/*
 * The guests, as the office edits them.
 *
 * Content is written in English and Romanian falls back to it, so only the
 * English name and role are required — a half-translated guest is better than
 * a missing one, and the public page already knows how to fall back.
 *
 * The Drive sync writes the same rows. It leaves an edited description alone,
 * so editing here is safe; a photo uploaded here is replaced next time that
 * guest's Drive folder gains a new one.
 */
class SpeakerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/2026/Speakers', [
            'speakers' => Person::with('translations')
                ->withCount('sessions')
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn (Person $p) => [
                    'id' => $p->id,
                    'name' => $p->full_name,
                    'slug' => $p->slug,
                    'avatar' => $p->avatar,
                    'position' => $p->position,
                    'role' => $p->translate('en')?->role ?? '',
                    'institution' => $p->translate('en')?->institution ?? '',
                    'sessions' => $p->sessions_count,
                    // Which languages actually have text, so gaps are visible
                    // from the list rather than only inside the form.
                    'locales' => $p->translations->filter(fn ($t) => filled($t->description))->pluck('locale')->values(),
                    'fromDrive' => $p->drive_folder_id !== null,
                ]),
            'publicBase' => Front2026Controller::BASE,
        ]);
    }

    public function create(): Response
    {
        return $this->form(new Person);
    }

    public function edit(Person $speaker): Response
    {
        return $this->form($speaker);
    }

    public function store(Request $request): RedirectResponse
    {
        $person = new Person;
        $this->save($person, $request);

        return redirect()
            ->route('admin.2026.speakers')
            ->with('flash', $person->full_name.' added.');
    }

    public function update(Request $request, Person $speaker): RedirectResponse
    {
        $this->save($speaker, $request);

        return redirect()
            ->route('admin.2026.speakers')
            ->with('flash', $speaker->full_name.' saved.');
    }

    public function destroy(Person $speaker): RedirectResponse
    {
        $name = $speaker->full_name;

        // The sessions survive; only the billing on them goes.
        $speaker->sessions()->detach();
        $speaker->delete();

        return redirect()
            ->route('admin.2026.speakers')
            ->with('flash', $name.' removed.');
    }

    private function form(Person $person): Response
    {
        return Inertia::render('Admin/2026/SpeakerForm', [
            'speaker' => [
                'id' => $person->id,
                'full_name' => $person->full_name ?? '',
                'slug' => $person->exists ? $person->slug : '',
                'avatar' => $person->avatar ?? '',
                'position' => $person->position ?? 0,
                'en' => $this->translation($person, 'en'),
                'ro' => $this->translation($person, 'ro'),
                'sessions' => $person->exists
                    ? $person->sessions()->with('translations')->get()->map(fn ($s) => $s->title)->all()
                    : [],
            ],
            'publicBase' => Front2026Controller::BASE,
        ]);
    }

    private function translation(Person $person, string $locale): array
    {
        $t = $person->exists ? $person->translate($locale) : null;

        return [
            'role' => $t->role ?? '',
            'institution' => $t->institution ?? '',
            'description' => $t->description ?? '',
        ];
    }

    private function save(Person $person, Request $request): void
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('wcm_2026.people', 'slug')->ignore($person->id),
            ],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'en.role' => ['nullable', 'string', 'max:255'],
            'en.institution' => ['nullable', 'string', 'max:255'],
            'en.description' => ['nullable', 'string'],
            'ro.role' => ['nullable', 'string', 'max:255'],
            'ro.institution' => ['nullable', 'string', 'max:255'],
            'ro.description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:8192'],
        ]);

        $person->full_name = $data['full_name'];
        // The slug is the profile's public URL, so it is only invented once.
        $person->slug = $data['slug'] ?: ($person->exists ? $person->slug : Str::slug($data['full_name']));
        $person->position = $data['position'] ?? 0;
        $person->save();

        foreach (['en', 'ro'] as $locale) {
            $t = $person->translateOrNew($locale);
            $t->role = $data[$locale]['role'] ?? '';
            $t->institution = $data[$locale]['institution'] ?? '';
            $t->description = $data[$locale]['description'] ?? null;
        }

        $person->save();

        if ($request->hasFile('photo')) {
            $person->avatar = GuestPhoto::store($person->slug, file_get_contents($request->file('photo')->getRealPath()));
            $person->save();
        }
    }
}
