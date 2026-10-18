<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\JournalistProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the one journalist profile (ADMIN_PANEL.md §12). Runs after the owner
 * account so the profile is attached to it; the model's `creating` guard means
 * this can never produce a second row.
 */
class JournalistProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (JournalistProfile::query()->exists()) {
            $this->command?->warn('A journalist profile already exists; leaving it untouched.');

            return;
        }

        $owner = User::query()->where('role', UserRole::Owner->value)->oldest('id')->first();

        if ($owner === null) {
            $this->command?->warn('No owner account found; run AdminUserSeeder first.');

            return;
        }

        $owner->journalistProfile()->create([
            'name' => $owner->name,
            'title' => 'Investigative Journalist',
            'short_bio' => null,
            'long_bio' => null,
            'meta' => ['beats' => [], 'social' => []],
        ]);

        $this->command?->info('Journalist profile ready for '.$owner->email.'.');
    }
}
