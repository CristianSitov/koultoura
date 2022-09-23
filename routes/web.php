<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;

//Route::get('/', [FrontController::class, 'splash']);

Route::get('/', [FrontController::class, 'index'])
    ->name('home');
Route::get('/registration', [FrontController::class, 'registration'])
    ->name('registration');
Route::post('/event-registration', [FrontController::class, 'eventRegistration'])
    ->name('event-registration');
Route::get('/user/{id}', [FrontController::class, 'confirmation'])
    ->name('confirmation');
Route::get('/{locale}', [FrontController::class, 'switchLocale'])
    ->where('locale', 'en|ro')
    ->name('locale');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [FrontController::class, 'dashboard'])
        ->name('dashboard');
    Route::get('/dashboard/subscribers', [FrontController::class, 'subscribersList'])
        ->name('dashboard_subscribers');
    Route::get('/dashboard/subscribers.pdf', [FrontController::class, 'subscribersListPdf'])
        ->name('dashboard_subscribers_pdf');
});

