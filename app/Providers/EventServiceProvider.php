<?php

namespace App\Providers;

use App\Support\Slack;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Who is in the backoffice, and who tried to be. Only a handful of
         * people have an account, so a sign-in is a rare enough event to be
         * worth seeing, and a failed one against a real address is worth
         * seeing twice.
         */
        Event::listen(function (Login $event) {
            Slack::info('Signed in to the backoffice', [
                'Who' => $event->user->email ?? '(unknown)',
                'From' => Request::ip(),
            ]);
        });

        Event::listen(function (Failed $event) {
            Slack::throttled(
                'login-failed:'.($event->credentials['email'] ?? Request::ip()),
                'Failed sign-in attempt',
                [
                    'Tried' => $event->credentials['email'] ?? '(no address)',
                    'From' => Request::ip(),
                    'Note' => $event->user ? 'A real account — wrong password.' : 'No account with that address.',
                ]
            );
        });

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
