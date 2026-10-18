<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReordersRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\StoreAwardRequest;
use App\Http\Requests\Admin\UpdateAwardRequest;
use App\Models\Award;
use App\Models\JournalistProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Awards and recognition (ADMIN_PANEL.md §12), attached to the singleton profile.
 */
class AwardController extends Controller
{
    use ReordersRecords;

    public function index(): View
    {
        $profile = JournalistProfile::current();

        return view('admin.awards.index', [
            'profile' => $profile,
            'entries' => $profile?->awards()->get() ?? collect(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (JournalistProfile::current() === null) {
            return $this->missingProfile();
        }

        return view('admin.awards.create', ['entry' => new Award]);
    }

    public function store(StoreAwardRequest $request): RedirectResponse
    {
        $profile = JournalistProfile::current();

        if ($profile === null) {
            return $this->missingProfile();
        }

        $profile->awards()->create([
            ...$request->validated(),
            'sort_order' => ($profile->awards()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.awards.index')->with('status', 'Award added.');
    }

    public function edit(Award $award): View
    {
        return view('admin.awards.edit', ['entry' => $award]);
    }

    public function update(UpdateAwardRequest $request, Award $award): RedirectResponse
    {
        $award->update($request->validated());

        return redirect()->route('admin.awards.index')->with('status', 'Award updated.');
    }

    public function destroy(Award $award): RedirectResponse
    {
        $award->delete();

        return redirect()->route('admin.awards.index')->with('status', 'Award removed.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->applyOrder(Award::class, $request->validated('ids'));

        return $this->reordered();
    }

    private function missingProfile(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')->withErrors([
            'profile' => 'Set up the journalist profile before adding awards.',
        ]);
    }
}
