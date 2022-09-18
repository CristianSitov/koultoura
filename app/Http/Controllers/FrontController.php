<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Presentation;
use App\Models\Person;
use Inertia\Inertia;
use Inertia\Response;

class FrontController extends Controller
{
    public function index(): Response
    {
        app()->setLocale('en');

        $days = Day::query()
            ->with(['host'])
            ->get()
            ->keyBy('id');
        $speakers = Person::query()
            ->selectRaw('people.*')
            ->leftJoin('person_presentation', 'person_presentation.person_id', '=', 'people.id')
            ->whereNull('person_presentation.id')
            ->get();
        $persons =
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
        ]);
    }
}
