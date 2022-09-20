<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [FrontController::class, 'splash'])
    ->name('home');
Route::get('/wcm', [FrontController::class, 'index'])
    ->name('home');

Route::get('/{locale}', [FrontController::class, 'locale'])
    ->where('locale', 'en|ro');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [FrontController::class, 'dashboard'])->name('dashboard');
});

