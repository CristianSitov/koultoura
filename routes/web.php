<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front2022Controller;
use App\Http\Controllers\Front2024Controller;
use Illuminate\Support\Facades\Route;

Route::name('2024.')
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
