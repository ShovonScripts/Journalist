<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateJournalistProfileRequest;
use App\Models\JournalistProfile;
use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The singleton author profile (ADMIN_PANEL.md §12). Edit-only: there is no
 * index and no create action, so no URL exists on which a second profile could
 * be made — reinforced by the model's `creating` guard.
 */
class JournalistProfileController extends Controller
{
    public function edit(JournalistProfile $journalistProfile): View
    {
        return view('admin.journalist-profile.edit', [
            'profile' => $journalistProfile,
            'publications' => Publication::query()->orderBy('sort_order')->orderBy('name')->get(),
            'social' => $journalistProfile->social(),
            'beats' => implode(', ', $journalistProfile->beats()),
        ]);
    }

    public function update(UpdateJournalistProfileRequest $request, JournalistProfile $journalistProfile): RedirectResponse
    {
        $data = $request->safe()->except(['beats', 'social', 'publications']);

        $data['meta'] = [
            'beats' => $this->parseBeats($request->input('beats')),
            'social' => array_filter($request->input('social', []), fn ($value) => filled($value)),
        ];

        $journalistProfile->update($data);
        $journalistProfile->publications()->sync($request->input('publications', []));

        return redirect()
            ->route('admin.journalist-profile.edit', $journalistProfile)
            ->with('status', 'Profile updated.');
    }

    /**
     * @return list<string>
     */
    private function parseBeats(?string $value): array
    {
        return Str::of((string) $value)
            ->explode(',')
            ->map(fn (string $beat) => trim($beat))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
