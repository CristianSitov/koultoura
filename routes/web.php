<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front2022Controller;
use App\Http\Controllers\Front2024Controller;
use App\Http\Controllers\Front2026Controller;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\Front2026RegistrationController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
 * The announcement page. When the 2026 landing page below takes over, this
 * becomes a redirect:
 *
 *     Route::redirect('/', Front2026Controller::BASE)->name('home');
 *
 * and the noindex in Pages/2026/Landing.vue comes off — at which point the
 * secret segment in Front2026Controller::PATH should go too.
 */
Route::controller(Front2026Controller::class)
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/en', 'en')->name('home.en');
        Route::get('/ro', 'ro')->name('home.ro');
    });

/*
 * Stripe's webhook. Outside the 2026 group on purpose: that prefix carries a
 * secret segment which may be rotated, and an address Stripe holds should not
 * move when it is.
 */
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::prefix(Front2026Controller::PATH)
    ->name('2026.')
    ->group(function () {
        Route::controller(Front2026Controller::class)
            ->group(function () {
                // The bare path is the English page, /ro the Romanian one.
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

        // Registration. The confirm link carries its own language (the one the
        // registration was made in), so it needs no locale variant.
        Route::controller(Front2026RegistrationController::class)
            ->group(function () {
                Route::get('/register', 'create')->name('register');
                Route::get('/{locale}/register', 'create')
                    ->where('locale', 'en|ro')
                    ->name('locale.register');
                Route::post('/register', 'store')
                    ->middleware('throttle:10,60')
                    ->name('register.store');
                /*
                 * The token is in the address so this page survives a reload
                 * and can be returned to from Stripe. It used to rely on a
                 * flashed session value, which is consumed by the first render
                 * — so a refresh, or coming back from checkout, lost the email
                 * and the contribute button with it.
                 */
                Route::get('/registered/{token}', 'submitted')
                    ->where('token', '[A-Za-z0-9]+')
                    ->name('registered');
                Route::get('/{locale}/registered/{token}', 'submitted')
                    ->where(['locale' => 'en|ro', 'token' => '[A-Za-z0-9]+'])
                    ->name('locale.registered');
                Route::post('/resend', 'resendConfirmation')
                    ->middleware('throttle:5,60')
                    ->name('resend');
                Route::get('/confirm/{token}', 'confirm')
                    ->where('token', '[A-Za-z0-9]+')
                    ->name('confirm');
                Route::get('/{locale}/confirm/{token}', 'confirm')
                    ->where(['locale' => 'en|ro', 'token' => '[A-Za-z0-9]+'])
                    ->name('locale.confirm');
            });

        // The contribution step. The language comes from the registration, so
        // these need no locale variant.
        Route::controller(ContributionController::class)
            ->middleware('throttle:20,60')
            ->group(function () {
                Route::get('/contribute/{token}', 'show')
                    ->where('token', '[A-Za-z0-9]+')
                    ->name('contribute');
                Route::get('/contribute/{token}/redirect', 'redirectToStripe')
                    ->where('token', '[A-Za-z0-9]+')
                    ->name('contribute.redirect');
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
        // The subscribers this lists are 2024's, and nothing in /dashboard
        // says so — without this it reads the current edition's database,
        // which has no users at all.
        'year:2024',
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
