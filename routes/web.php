<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front2022Controller;
use App\Http\Controllers\Front2024Controller;
use App\Http\Controllers\Front2026Controller;
use Illuminate\Support\Facades\Route;

/*
 * The announcement page. When the 2026 landing page below takes over, this
 * becomes a redirect:
 *
 *     Route::redirect('/', '/2026')->name('home');
 *
 * and the noindex in Pages/2026/Landing.vue comes off.
 */
Route::controller(Front2026Controller::class)
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/en', 'en')->name('home.en');
        Route::get('/ro', 'ro')->name('home.ro');
    });

Route::prefix('2026')
    ->name('2026.')
    ->group(function () {
        Route::controller(Front2026Controller::class)
            ->group(function () {
                // /2026 is the English page; /2026/ro is the Romanian one.
                // Resolve2026Locale sends first-time Romanian visitors across.
                Route::get('/', 'landing')->name('home');
                Route::get('/guests/{slug}', 'guest')
                    ->where('slug', '[a-z0-9-]+')
                    ->name('guest');
                Route::get('/{locale}', 'landing')
                    ->where('locale', 'en|ro')
                    ->name('locale');
                Route::get('/{locale}/guests/{slug}', 'guest')
                    ->where(['locale' => 'en|ro', 'slug' => '[a-z0-9-]+'])
                    ->name('locale.guest');
            });
    });

Route::prefix('2024')
    ->name('2024.')
    ->group(function () {
       Route::controller(Front2024Controller::class)
           ->group(function () {
               Route::get('/', 'index')
                   ->name('home');
               Route::get('/registration', 'registration')
                   ->name('registration');
               Route::post('/event-registration', 'eventRegistration')
                   ->name('event-registration');
               Route::get('/user/{id}', 'confirmation')
                   ->name('confirmation');
               Route::get('/{locale}', 'switchLocale')
                   ->where('locale', 'en|ro')
                   ->name('locale');
               Route::get('/terms', 'terms')
                   ->name('terms');
               Route::get('/cookies', 'cookies')
                   ->name('cookies');
           });
    });

Route::prefix('2022')
    ->name('2022.')
    ->group(function () {
        Route::controller(Front2022Controller::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->name('home');
                Route::get('/schedule', 'schedule')
                    ->name('schedule');
                Route::get('/{locale}', 'switchLocale')
                    ->where('locale', 'en|ro')
                    ->name('locale');
                Route::get('/terms', 'terms')
                    ->name('terms');
                Route::get('/cookies', 'cookies')
                    ->name('cookies');
            });
    });

Route::controller(DashboardController::class)
    ->middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])
    ->group(function () {
        Route::get('/dashboard', 'dashboard')
            ->name('dashboard');
        Route::get('/dashboard/subscribers/{user_id}/reconfirm', 'reconfirmUser')
            ->name('dashboard_subscribers_reconfirm');
        Route::get('/dashboard/subscribers/{day?}/{volunteers?}', 'subscribersList')
            ->name('dashboard_subscribers');
        Route::get('/dashboard/subscribers.pdf', 'subscribersListPdf')
            ->name('dashboard_subscribers_pdf');
    });
