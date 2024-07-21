<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LandingController extends Controller
{
    public function splash()
    {
        return Inertia::render('LatestSplash');
    }
}
