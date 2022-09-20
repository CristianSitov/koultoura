<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Presentation;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class FrontController extends Controller
{
    public function splash()
    {
//        if (Carbon::now()->lt(Carbon::parse('2022-09-20 18:00:00'))) {
            return view('welcome');
//        }
    }

    public function index(): Response
    {
        $translation = array_values(array_diff(config('translatable.locales'), [app()->getLocale()]))[0];

        $days = Day::query()
            ->with(['host'])
            ->get()
            ->keyBy('id');
        $speakers = Person::query()
            ->selectRaw('people.*')
            ->leftJoin('person_presentation', 'person_presentation.person_id', '=', 'people.id')
            ->whereNull('person_presentation.id')
            ->get();
        $presentations = Presentation::with([
            'speakers',
            'moderators',
        ])
            ->get()
            ->groupBy('day_id');

        return Inertia::render('Home', [
            'days' => $days,
            'speakers' => $speakers,
            'presentations' => $presentations,
            'translation' => $translation,
        ]);
    }

    public function locale($language = 'en'): RedirectResponse
    {
        if (in_array($language, config('translatable.locales'), true)) {
            app()->setLocale($language);
            session()->put('locale', $language);
        }

        return redirect()->back();
    }

    public function dashboard(): Response
    {
        return Inertia::render('Dashboard');
    }
}
