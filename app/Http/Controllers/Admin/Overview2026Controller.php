<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Models\Contribution;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Registration;
use App\Models\Session;
use Inertia\Inertia;
use Inertia\Response;

/** What state the edition is in, on one screen. */
class Overview2026Controller extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/2026/Overview', [
            'stats' => [
                'speakers' => Person::count(),
                'days' => ProgrammeDay::count(),
                'sessions' => Session::count(),
                // The gap between these two is the work still to do.
                'sessionsPublished' => Session::where('published', true)->count(),
                'registrations' => Registration::count(),
                'confirmed' => Registration::whereNotNull('confirmed_at')->count(),
                'contributions' => Contribution::where('status', 'paid')->count(),
                'contributed' => Contribution::where('status', 'paid')->sum('amount') / 100,
                'bookable' => Session::where('bookable', true)->count(),
            ],
            'publicBase' => Front2026Controller::BASE,
        ]);
    }
}
