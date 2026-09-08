<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

/** Take backoffice access away without touching the person's row otherwise. */
class RevokeAdmin extends Command
{
    protected $signature = 'admin:revoke {email}';

    protected $description = 'Remove a backoffice login';

    public function handle(): int
    {
        $admin = Admin::where('email', $this->argument('email'))->first();

        if (! $admin) {
            $this->error('No backoffice account for '.$this->argument('email'));

            return self::FAILURE;
        }

        $admin->forceFill(['backoffice' => false])->save();
        $this->info('Revoked '.$admin->email);

        return self::SUCCESS;
    }
}
