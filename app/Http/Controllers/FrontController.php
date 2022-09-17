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
//dd(Person::all()->first()->toArray());
        return Inertia::render('Home', [
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
                        'supratitle' => $item->supratitle,
                        'subtitle' => $item->subtitle,
                        'description' => $item->description,
                        'flag' => $item->flag,
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
