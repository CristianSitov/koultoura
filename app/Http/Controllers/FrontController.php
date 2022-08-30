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

        return Inertia::render('Welcome', [
            'days' => Day::all()->keyBy('id'),
            'presentations' => Presentation::with([
                    'speakers',
                    'moderators',
                ])
                ->get()
                ->transform(static function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'subtitle' => $item->subtitle,
                        'description' => $item->description,
                        'hour' => $item->starts_at->format('H:i'),
                        'day_id' => $item->day_id,
                        'speakers' => $item->speakers,
                    ];
                })
                ->groupBy('day_id'),
            'speakers' => Person::all()
        ]);
    }
}
