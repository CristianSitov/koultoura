<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class Front2026Controller extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('2026/Home');
    }
}
