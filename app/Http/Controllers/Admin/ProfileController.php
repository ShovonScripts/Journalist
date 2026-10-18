<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasscodeRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * The account screen (ADMIN_PANEL.md §12): the *login* identity — name, email
 * and passcode — as opposed to the public author profile.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('status', 'Account updated.');
    }

    public function updatePasscode(UpdatePasscodeRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_passcode'), $user->password)) {
            return back()->withErrors([
                'current_passcode' => 'That does not match your current passcode.',
            ]);
        }

        $user->forceFill(['password' => $request->input('passcode')])->save();

        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'profile.passcode_changed',
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
        ]);

        return back()->with('status', 'Passcode changed.');
    }
}
