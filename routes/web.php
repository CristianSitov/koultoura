<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


//Route::get('/', [FrontController::class, 'splash']);

Route::get('/', [FrontController::class, 'index'])
    ->name('home');

Route::get('/registration', [FrontController::class, 'registration'])
    ->name('registration');
Route::get('/user/{id}', [FrontController::class, 'confirmation'])
    ->name('confirmation');

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
    Route::get('/dashboard', static function () {
        return redirect('/');
    });
});

