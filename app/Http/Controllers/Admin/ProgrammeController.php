<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/*
 * The programme: themes, days and the sessions inside them.
 *
 * Everything is drafted in the open. A session appears on the public page only
 * when it is published, and so does a day, so the schedule can be rearranged
 * for weeks without anyone watching it happen.
 */
class ProgrammeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/2026/Programme', [
            'days' => ProgrammeDay::with(['translations', 'theme.translations', 'sessions.translations', 'sessions.speakers'])
                ->orderBy('position')
                ->orderBy('date')
                ->get()
                ->map(fn (ProgrammeDay $day) => [
                    'id' => $day->id,
                    'date' => $day->date->toDateString(),
                    'name' => $day->translate('en')?->name ?? '',
                    'name_ro' => $day->translate('ro')?->name ?? '',
                    'position' => $day->position,
                    'published' => $day->published,
                    'theme_id' => $day->theme_id,
                    'theme' => $day->theme ? $day->theme->numeral.' · '.($day->theme->translate('en')?->title ?? '') : null,
                    'sessions' => $day->sessions->map(fn (Session $s) => [
                        'id' => $s->id,
                        'time' => substr($s->starts_at, 0, 5),
                        'kind' => $s->kind,
                        'title' => $s->translate('en')?->title ?? '',
                        'who' => $s->speakers->pluck('full_name')->implode(', ')
                            ?: ($s->translate('en')?->audience ?? ''),
                        'school' => $s->school,
                        'published' => $s->published,
                        'bookable' => $s->bookable,
                        'capacity' => $s->capacity,
                        'taken' => $s->bookable ? $s->taken() : null,
                    ]),
                ]),
            'themes' => Theme::with('translations')->orderBy('position')->get()
                ->map(fn (Theme $t) => [
                    'id' => $t->id,
                    'numeral' => $t->numeral,
                    'title' => $t->translate('en')?->title ?? '',
                    'title_ro' => $t->translate('ro')?->title ?? '',
                    'description' => $t->translate('en')?->description ?? '',
                    'description_ro' => $t->translate('ro')?->description ?? '',
                    'position' => $t->position,
                ]),
            'visible' => Setting::bool(Setting::PROGRAMME_VISIBLE),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /**
     * Show or hide the whole section on the public page.
     *
     * Separate from publishing a session: this is "is there a programme to
     * look at yet", and while it is off nothing inside it matters.
     */
    public function toggleVisibility(): RedirectResponse
    {
        $visible = ! Setting::bool(Setting::PROGRAMME_VISIBLE);
        Setting::put(Setting::PROGRAMME_VISIBLE, $visible);

        return back()->with('flash', $visible
            ? 'The programme is now on the public page.'
            : 'The programme is hidden from the public page.');
    }

    /* ---------------------------------------------------------------- days */

    public function storeDay(Request $request): RedirectResponse
    {
        $this->saveDay(new ProgrammeDay, $request);

        return back()->with('flash', 'Day added.');
    }

    public function updateDay(Request $request, ProgrammeDay $day): RedirectResponse
    {
        $this->saveDay($day, $request);

        return back()->with('flash', 'Day saved.');
    }

    public function destroyDay(ProgrammeDay $day): RedirectResponse
    {
        // Its sessions go with it — the migration cascades, and a session
        // without a day has nowhere to appear.
        $day->delete();

        return back()->with('flash', 'Day removed.');
    }

    private function saveDay(ProgrammeDay $day, Request $request): void
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'theme_id' => ['nullable', Rule::exists('wcm_2026.themes', 'id')],
            'position' => ['nullable', 'integer', 'min:0', 'max:99'],
            'published' => ['boolean'],
            'name' => ['nullable', 'string', 'max:255'],
            'name_ro' => ['nullable', 'string', 'max:255'],
        ]);

        $day->fill([
            'date' => $data['date'],
            'theme_id' => $data['theme_id'] ?? null,
            'position' => $data['position'] ?? 0,
            'published' => $data['published'] ?? false,
        ])->save();

        $day->translateOrNew('en')->name = $data['name'] ?? '';
        $day->translateOrNew('ro')->name = $data['name_ro'] ?? '';
        $day->save();
    }

    /* -------------------------------------------------------------- themes */

    public function saveTheme(Request $request, ?Theme $theme = null): RedirectResponse
    {
        $data = $request->validate([
            'numeral' => ['required', 'string', 'max:8'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99'],
            'title' => ['required', 'string', 'max:255'],
            'title_ro' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ro' => ['nullable', 'string'],
        ]);

        $theme ??= new Theme;
        $theme->fill(['numeral' => $data['numeral'], 'position' => $data['position'] ?? 0])->save();

        $en = $theme->translateOrNew('en');
        $en->title = $data['title'];
        $en->description = $data['description'] ?? null;

        $ro = $theme->translateOrNew('ro');
        $ro->title = $data['title_ro'] ?? '';
        $ro->description = $data['description_ro'] ?? null;

        $theme->save();

        return back()->with('flash', 'Theme saved.');
    }

    /* ------------------------------------------------------------ sessions */

    public function createSession(Request $request): Response
    {
        $session = new Session(['programme_day_id' => $request->integer('day')]);

        return $this->sessionForm($session);
    }

    public function editSession(Session $session): Response
    {
        return $this->sessionForm($session);
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $session = new Session;
        $this->saveSession($session, $request);

        return redirect()->route('admin.2026.programme')->with('flash', 'Session added.');
    }

    public function updateSession(Request $request, Session $session): RedirectResponse
    {
        $this->saveSession($session, $request);

        return redirect()->route('admin.2026.programme')->with('flash', 'Session saved.');
    }

    public function destroySession(Session $session): RedirectResponse
    {
        if ($session->bookings()->whereNull('cancelled_at')->exists()) {
            return back()->withErrors([
                'session' => 'People hold places in this session. Cancel their bookings first, or unpublish it instead.',
            ]);
        }

        $session->delete();

        return back()->with('flash', 'Session removed.');
    }

    /** The one-click toggle from the list, rather than opening the form. */
    public function toggleSession(Session $session): RedirectResponse
    {
        $session->published = ! $session->published;
        $session->save();

        return back()->with('flash', $session->published ? 'Session published.' : 'Session hidden.');
    }

    private function sessionForm(Session $session): Response
    {
        return Inertia::render('Admin/2026/SessionForm', [
            'session' => [
                'id' => $session->id,
                'programme_day_id' => $session->programme_day_id,
                'starts_at' => substr($session->starts_at ?? '10:00', 0, 5),
                'ends_at' => $session->ends_at ? substr($session->ends_at, 0, 5) : '',
                'kind' => $session->kind ?? '',
                'school' => (bool) $session->school,
                'published' => (bool) $session->published,
                'position' => $session->position ?? 0,
                'bookable' => (bool) $session->bookable,
                'capacity' => $session->capacity,
                'slug' => $session->slug ?? '',
                'speakers' => $session->exists ? $session->speakers->pluck('id')->all() : [],
                'en' => $this->sessionText($session, 'en'),
                'ro' => $this->sessionText($session, 'ro'),
                'taken' => $session->exists && $session->bookable ? $session->taken() : 0,
            ],
            'days' => ProgrammeDay::with('translations')->orderBy('position')->get()
                ->map(fn ($d) => ['id' => $d->id, 'label' => $d->date->format('D d M').' — '.($d->translate('en')?->name ?? '')]),
            'people' => Person::orderBy('full_name')->get(['id', 'full_name'])
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->full_name]),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    private function sessionText(Session $session, string $locale): array
    {
        $t = $session->exists ? $session->translate($locale) : null;

        return [
            'title' => $t->title ?? '',
            'subtitle' => $t->subtitle ?? '',
            'audience' => $t->audience ?? '',
            'description' => $t->description ?? '',
        ];
    }

    private function saveSession(Session $session, Request $request): void
    {
        $data = $request->validate([
            'programme_day_id' => ['required', Rule::exists('wcm_2026.programme_days', 'id')],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'kind' => ['nullable', 'string', 'max:255'],
            'school' => [
                'boolean',
                /*
                 * The Heritage School runs on three of the four days. Guarding
                 * it here rather than only hiding the checkbox: the day and the
                 * flag are two separate fields, and moving a workshop onto the
                 * 8th afterwards would otherwise slip straight through.
                 */
                function (string $attribute, $value, callable $fail) use ($request) {
                    if (! $value) {
                        return;
                    }

                    $day = ProgrammeDay::find($request->input('programme_day_id'));

                    if ($day && ! $day->hostsSchool()) {
                        $fail('The Heritage School only runs on '
                            .implode(', ', ProgrammeDay::SCHOOL_DAYS).' October.');
                    }
                },
            ],
            'published' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99'],
            'bookable' => ['boolean'],
            // Capacity is what makes a booking form finite; required with one.
            'capacity' => ['nullable', 'required_if:bookable,true', 'integer', 'min:1', 'max:1000'],
            'slug' => [
                'nullable', 'required_if:bookable,true', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('wcm_2026.sessions', 'slug')->ignore($session->id),
            ],
            'speakers' => ['array'],
            'speakers.*' => [Rule::exists('wcm_2026.people', 'id')],
            'en.title' => ['required', 'string', 'max:255'],
            'en.subtitle' => ['nullable', 'string', 'max:255'],
            'en.audience' => ['nullable', 'string', 'max:255'],
            'en.description' => ['nullable', 'string'],
            'ro.title' => ['nullable', 'string', 'max:255'],
            'ro.subtitle' => ['nullable', 'string', 'max:255'],
            'ro.audience' => ['nullable', 'string', 'max:255'],
            'ro.description' => ['nullable', 'string'],
        ]);

        $bookable = $data['bookable'] ?? false;

        $session->fill([
            'programme_day_id' => $data['programme_day_id'],
            'starts_at' => $data['starts_at'].':00',
            'ends_at' => filled($data['ends_at'] ?? null) ? $data['ends_at'].':00' : null,
            'kind' => $data['kind'] ?? '',
            'school' => $data['school'] ?? false,
            'published' => $data['published'] ?? false,
            'position' => $data['position'] ?? 0,
            'bookable' => $bookable,
            'capacity' => $bookable ? $data['capacity'] : null,
            // A slug that has been handed out in a link is kept even if booking
            // is switched off, so the address does not rot.
            'slug' => filled($data['slug'] ?? null)
                ? $data['slug']
                : ($bookable ? Str::slug($data['en']['title']) : $session->slug),
        ])->save();

        foreach (['en', 'ro'] as $locale) {
            $t = $session->translateOrNew($locale);
            $t->title = $data[$locale]['title'] ?? '';
            $t->subtitle = $data[$locale]['subtitle'] ?? null;
            $t->audience = $data[$locale]['audience'] ?? null;
            $t->description = $data[$locale]['description'] ?? null;
        }

        $session->save();

        $session->speakers()->sync(
            collect($data['speakers'] ?? [])
                ->values()
                ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i + 1]])
                ->all()
        );
    }
}
