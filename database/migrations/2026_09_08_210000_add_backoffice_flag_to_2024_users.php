<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Who may sign into the backoffice.
 *
 * The guard reads the 2024 `users` table, which is also that edition's
 * subscriber list — so every row there carrying a password hash was a working
 * login, forty-seven of them. That was never intended: they are people who
 * signed up for an event, not organisers.
 *
 * A flag rather than clearing those passwords: the rows belong to the 2024
 * edition and are not ours to empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2024')->table('users', function (Blueprint $table) {
            $table->boolean('backoffice')->default(false)->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::connection('wcm_2024')->table('users', function (Blueprint $table) {
            $table->dropColumn('backoffice');
        });
    }
};
