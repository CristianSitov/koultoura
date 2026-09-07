<?php

use App\Http\Controllers\Admin\Overview2026Controller;
use App\Http\Controllers\Admin\ProgrammeController;
use App\Http\Controllers\Admin\RegistrationsController;
use App\Http\Controllers\Admin\SpeakerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front2022Controller;
use App\Http\Controllers\Front2024Controller;
use App\Http\Controllers\Front2026Controller;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\Front2026RegistrationController;
use App\Http\Controllers\Front2026SessionController;
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

        /*
         * A place in a capped session — its own small form, because the day
         * registration does not cover it and the places run out.
         */
        Route::controller(Front2026SessionController::class)
            ->group(function () {
                Route::get('/sessions/{slug}', 'show')
                    ->where('slug', '[a-z0-9-]+')
                    ->name('session');
                Route::get('/{locale}/sessions/{slug}', 'show')
                    ->where(['locale' => 'en|ro', 'slug' => '[a-z0-9-]+'])
                    ->name('locale.session');
                Route::post('/sessions/{slug}', 'store')
                    ->where('slug', '[a-z0-9-]+')
                    ->middleware('throttle:10,60')
                    ->name('session.book');
                Route::post('/{locale}/sessions/{slug}', 'store')
                    ->where(['locale' => 'en|ro', 'slug' => '[a-z0-9-]+'])
                    ->middleware('throttle:10,60')
                    ->name('locale.session.book');
                Route::get('/sessions/{slug}/booked/{token}', 'booked')
                    ->where(['slug' => '[a-z0-9-]+', 'token' => '[A-Za-z0-9]+'])
                    ->name('session.booked');
                Route::get('/{locale}/sessions/{slug}/booked/{token}', 'booked')
                    ->where(['locale' => 'en|ro', 'slug' => '[a-z0-9-]+', 'token' => '[A-Za-z0-9]+'])
                    ->name('locale.session.booked');
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

/*
 * The 2024 dashboard, now under its own year: /dashboard belongs to the
 * edition being run, and that is no longer 2024.
 *
 * `year:2024` because the subscribers it lists are that edition's, and nothing
 * in the address says so — without it these read the current database, which
 * has no users at all.
 */
Route::controller(DashboardController::class)
    ->prefix('dashboard/2024')
    ->middleware([
        'year:2024',
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])
    ->group(function () {
        Route::get('/', 'dashboard')
            ->name('dashboard_2024');
        Route::get('/subscribers/{user_id}/reconfirm', 'reconfirmUser')
            ->name('dashboard_subscribers_reconfirm');
        Route::get('/subscribers/{day?}/{volunteers?}', 'subscribersList')
            ->name('dashboard_subscribers');
        Route::get('/subscribers.pdf', 'subscribersListPdf')
            ->name('dashboard_subscribers_pdf');
    });

// Addresses that were handed out before the move. Bookmarks and old emails
// still work; nobody has to know the dashboard was rearranged.
Route::redirect('/dashboard/2026', '/dashboard');
Route::get('/dashboard/subscribers{rest?}', fn (string $rest = '') => redirect('/dashboard/2024/subscribers'.$rest))
    ->where('rest', '.*');

/*
 * The 2026 backoffice.
 *
 * `year:2026` rather than the dashboard's 2024: these screens read and write
 * the current edition's database. The login itself is unaffected — the guard
 * looks in a fixed place (App\Models\Admin), whatever the URL selects.
 */
Route::prefix('dashboard')
    ->name('admin.2026.')
    ->middleware([
        'year:2026',
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])
    ->group(function () {
        // /dashboard itself: the edition being run is the one you land on.
        Route::get('/', Overview2026Controller::class)->name('overview');

        Route::controller(SpeakerController::class)->group(function () {
            Route::get('/speakers', 'index')->name('speakers');
            Route::get('/speakers/new', 'create')->name('speakers.create');
            Route::post('/speakers', 'store')->name('speakers.store');
            Route::get('/speakers/{speaker}', 'edit')->name('speakers.edit');
            // POST, not PUT: the form carries a photo, and PHP only parses
            // multipart bodies on POST.
            Route::post('/speakers/{speaker}', 'update')->name('speakers.update');
            Route::delete('/speakers/{speaker}', 'destroy')->name('speakers.destroy');
        });

        Route::controller(ProgrammeController::class)->group(function () {
            Route::get('/programme', 'index')->name('programme');

            Route::post('/programme/days', 'storeDay')->name('days.store');
            Route::put('/programme/days/{day}', 'updateDay')->name('days.update');
            Route::delete('/programme/days/{day}', 'destroyDay')->name('days.destroy');

            Route::post('/programme/themes', 'saveTheme')->name('themes.store');
            Route::put('/programme/themes/{theme}', 'saveTheme')->name('themes.update');

            Route::get('/programme/sessions/new', 'createSession')->name('sessions.create');
            Route::post('/programme/sessions', 'storeSession')->name('sessions.store');
            Route::get('/programme/sessions/{session}', 'editSession')->name('sessions.edit');
            Route::put('/programme/sessions/{session}', 'updateSession')->name('sessions.update');
            Route::put('/programme/sessions/{session}/published', 'toggleSession')->name('sessions.toggle');
            Route::delete('/programme/sessions/{session}', 'destroySession')->name('sessions.destroy');
        });

        Route::controller(RegistrationsController::class)->group(function () {
            Route::get('/registrations', 'index')->name('registrations');
            Route::get('/registrations.csv', 'export')->name('registrations.export');
            Route::post('/registrations/{registration}/resend', 'resendConfirmation')->name('registrations.resend');
            Route::post('/registrations/{registration}/confirm', 'confirm')->name('registrations.confirm');

            Route::get('/bookings', 'bookings')->name('bookings');
            Route::post('/bookings/{booking}/cancel', 'cancelBooking')->name('bookings.cancel');
        });
    });
