<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
 * A login for the backoffice.
 *
 * Accounts are made here rather than through a public form — there is no
 * sign-up page, on purpose. The row goes where the guard looks: the 2024
 * database, whatever URL is being visited (see App\Models\Admin).
 *
 * The address is marked verified immediately: the dashboard is behind
 * `verified`, and there is nobody to click a link in a local inbox.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {password} {--name=}';

    protected $description = 'Create or update a backoffice login';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (strlen($password) < 8) {
            $this->error('Use at least 8 characters.');

            return self::FAILURE;
        }

        $admin = Admin::firstOrNew(['email' => $email]);
        $existed = $admin->exists;

        $name = $this->option('name') ?: Str::headline(Str::before($email, '@'));

        // The 2024 users table splits the name and carries a slug; both are
        // required columns, and this account will never come through the form
        // that would otherwise fill them.
        $admin->forceFill([
            'name' => $name,
            'first_name' => Str::before($name, ' '),
            'last_name' => Str::after($name, ' ') ?: '',
            'slug' => $admin->slug ?: Str::slug($email),
            'password' => Hash::make($password),
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();

        $this->info(($existed ? 'Password updated for ' : 'Created ').$email);
        $this->line('Log in at /login — the 2026 backoffice is at /dashboard/2026');

        return self::SUCCESS;
    }
}
