<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Speaker;
use Inertia\Inertia;
use Inertia\Response;

class FrontController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'presentations' => Presentation::with('speakers')
                ->get()
                ->transform(static function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'hour' => $item->starts_at->format('H:i'),
                        'group' => $item->group,
                        'speakers' => $item->speakers,
                    ];
                })
                ->groupBy('group'),
            'speakers' => Speaker::all()
        ]);
    }
}
