<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the single owner account. The passcode is generated fresh and
 * printed exactly once — there is no default value to guess
 * (ADMIN_PANEL.md §16, §14).
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::query()->where('role', UserRole::Owner->value)->first();

        if ($existing !== null) {
            $this->command?->warn('An owner account already exists for '.$existing->email.'.');
            $this->command?->line('  Rotate its passcode with: php artisan admin:passcode');

            return;
        }

        $passcode = Str::password(length: 20);

        $user = new User([
            'name' => 'Emrul Hasan Bappi',
            'email' => 'owner@journalist.local',
            'password' => $passcode,
        ]);

        $user->forceFill(['role' => UserRole::Owner])->save();

        $this->command?->info('Owner account created: '.$user->email);
        $this->command?->line('  Sign-in passcode: <fg=yellow>'.$passcode.'</>');
        $this->command?->newLine();
        $this->command?->comment('Store this now. It is not shown again and is not recoverable — only rotatable.');
    }
}
