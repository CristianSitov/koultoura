<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * A speaker gets an address, and the language to write to them in — kept on
 * the speaker, where it belongs, and never shown on the site.
 *
 * The agenda's send list then links a row to its speaker, so every speaker
 * with an address is on the list without being typed in twice, and a changed
 * address follows through. `sent_email` records where the last send actually
 * went: when it stops matching the address on file, that person has not had
 * the programme at the address they now use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('full_name');
            $table->string('email_locale', 2)->default('en')->after('email');
        });

        Schema::connection('wcm_2026')->table('agenda_recipients', function (Blueprint $table) {
            $table->foreignId('person_id')->nullable()->unique()->after('id')
                ->constrained('people')->cascadeOnDelete();
            $table->string('sent_email')->nullable()->after('sent_count');
        });

        // Anyone already sent to was sent to the address they have now.
        DB::connection('wcm_2026')->statement('UPDATE agenda_recipients SET sent_email = email WHERE sent_count > 0');
    }

    public function down(): void
    {
        Schema::connection('wcm_2026')->table('agenda_recipients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('person_id');
            $table->dropColumn('sent_email');
        });

        Schema::connection('wcm_2026')->table('people', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'email_locale']);
        });
    }
};
