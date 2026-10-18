<?php

namespace App\Console\Commands;

use App\Support\PasscodeGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Recovery path for a lost passcode. Run on the server by someone with shell
 * access, which is the correct trust boundary for a single-owner panel
 * (SECURITY.md §3, ADMIN_PANEL.md §14). There is no "forgot passcode" email.
 */
class RotateAdminPasscode extends Command
{
    protected $signature = 'admin:passcode
                            {--generate : Generate a strong random passcode instead of prompting}';

    protected $description = 'Rotate the owner sign-in passcode';

    public function handle(): int
    {
        $owner = PasscodeGuard::owner();

        if ($owner === null) {
            $this->error('No owner account exists. Run: php artisan db:seed --class=AdminUserSeeder');

            return self::FAILURE;
        }

        if ($this->option('generate')) {
            $passcode = Str::password(length: 20);
        } else {
            $passcode = (string) $this->secret('New passcode');
            $confirmation = (string) $this->secret('Confirm new passcode');

            if ($passcode === '' || $passcode !== $confirmation) {
                $this->error('The passcodes did not match, or were empty.');

                return self::FAILURE;
            }
        }

        $owner->forceFill(['password' => $passcode])->save();

        $this->info('Passcode rotated for '.$owner->name.' <'.$owner->email.'>.');
        $this->line('  New passcode: <fg=yellow>'.$passcode.'</>');
        $this->newLine();
        $this->comment('Store this now. It is not shown again and is not recoverable — only rotatable.');

        return self::SUCCESS;
    }
}
