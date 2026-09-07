<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * What people gave. Written only by the Stripe webhook — never by the browser
 * coming back from checkout, which proves nothing: people close the tab, and
 * a redirect can be forged.
 *
 * `registration_id` is nullable on purpose. The payment link is a public URL;
 * somebody can reach it without a registration, or with a token that has since
 * been deleted, and a donation that arrives should be recorded either way.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            // Stripe's own id for the checkout session: the idempotency key for
            // a webhook that may be delivered more than once.
            $table->string('session_id')->unique();
            $table->string('payment_intent_id')->nullable();
            $table->string('email')->nullable();
            // Minor units, as Stripe sends them — 2500 is 25.00 RON.
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->string('status', 32);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->dropIfExists('contributions');
    }
};
