<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Constant-duration passcode check for the single-owner admin panel.
 *
 * Exactly one bcrypt comparison always runs — against a decoy hash when no
 * owner row exists — and the response is held to a fixed floor, so a missing
 * account and a wrong passcode are indistinguishable by response time.
 * See ADMIN_PANEL.md §14 and SECURITY.md §3.
 */
class PasscodeGuard
{
    private const MIN_DURATION_MICROSECONDS = 250_000;

    private const DECOY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    public static function owner(): ?User
    {
        return User::query()
            ->where('role', UserRole::Owner->value)
            ->oldest('id')
            ->first();
    }

    public static function attempt(string $passcode): ?User
    {
        $startedAt = microtime(true);

        $owner = self::owner();
        $hash = $owner?->password ?? self::DECOY_HASH;

        $matches = Hash::check($passcode, $hash);

        self::holdToFloor($startedAt);

        return ($matches && $owner !== null) ? $owner : null;
    }

    private static function holdToFloor(float $startedAt): void
    {
        $elapsed = (int) ((microtime(true) - $startedAt) * 1_000_000);
        $remaining = self::MIN_DURATION_MICROSECONDS - $elapsed;

        if ($remaining > 0) {
            usleep($remaining);
        }
    }
}
