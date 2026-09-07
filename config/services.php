<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
     * Read-only Drive access for `php artisan guests:sync`. An API key is
     * enough as long as the guests folder is shared with "anyone with the
     * link"; a private folder needs OAuth or a service account instead.
     */
    'google_drive' => [
        'key' => env('GOOGLE_DRIVE_API_KEY'),
        'guests_folder' => env('GOOGLE_DRIVE_GUESTS_FOLDER'),
    ],

    /*
     * Payment runs on Stripe-hosted payment links, so there is no API key here
     * — only the secret needed to verify that a webhook really came from
     * Stripe, and the links themselves.
     */
    'stripe' => [
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Checkout sessions: one product, the page's language, a real cancel
        // route. Needs the API secret and the id of the pay-what-you-want
        // price. Without both, the payment links below are used instead.
        'secret' => env('STRIPE_SECRET'),
        'price_id' => env('STRIPE_PRICE_ID'),
        'payment_link' => env('STRIPE_PAYMENT_LINK'),
        'payment_link_ro' => env('STRIPE_PAYMENT_LINK_RO'),
    ],

];
