<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('local')) {
            DB::listen(static function ($query) {
                Log::info(
                    $query->sql,
                    ['b' => $query->bindings, 't' => $query->time],
                );
            });
        }
    }
}
