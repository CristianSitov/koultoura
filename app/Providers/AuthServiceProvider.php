<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(static function ($notifiable, $url) {
            return (new MailMessage)
                ->subject(__('Why Culture Matters? // Verify Email Address'))
                ->line(__("In order to complete your registration at Why Culture Matters? International Symposium, we need to verify your email address. Please click the button below."))
                ->action(__('Verify Email Address'), $url);
        });
    }
}
