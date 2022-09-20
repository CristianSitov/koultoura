<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', static function () {
    return view('welcome');
});

Route::get('/wcm', [FrontController::class, 'index'])
    ->name('home');

Route::get('/{locale}', static function ($locale = null) {
    if (isset($locale) && in_array($locale, config('translatable.locales'), true)) {
        app()->setLocale($locale);
        session()->put('locale', $locale);
    }

    return redirect()->back();
})
    ->where('locale', 'en|ro');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

