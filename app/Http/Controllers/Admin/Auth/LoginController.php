<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Support\PasscodeGuard;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Passcode-only sign-in (SECURITY.md §1–§4, ADMIN_PANEL.md §14).
 *
 * One field, no email: the owner row is resolved server-side from
 * role = 'owner' and the request is never read for an identity.
 */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'passcode' => ['required', 'string'],
        ]);

        $user = PasscodeGuard::attempt((string) $request->input('passcode'));

        if ($user === null) {
            event(new Failed('web', null, ['passcode' => $request->input('passcode')]));

            return back()
                ->withInput()
                ->withErrors(['passcode' => 'That passcode is not correct.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));

        event(new Login('web', $user, false));

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function throttleKey(Request $request): string
    {
        return sha1('admin-login|'.$request->method().'|'.$request->ip());
    }
}
