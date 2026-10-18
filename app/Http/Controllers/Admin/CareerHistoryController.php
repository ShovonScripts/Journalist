<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReordersRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\StoreCareerHistoryRequest;
use App\Http\Requests\Admin\UpdateCareerHistoryRequest;
use App\Models\CareerHistory;
use App\Models\JournalistProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Career entries (ADMIN_PANEL.md §12). Rows always attach to
 * `JournalistProfile::current()` — the profile is never a user-editable select.
 */
class CareerHistoryController extends Controller
{
    use ReordersRecords;

    public function index(): View
    {
        $profile = JournalistProfile::current();

        return view('admin.career-history.index', [
            'profile' => $profile,
            'entries' => $profile?->careerHistory()->get() ?? collect(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (JournalistProfile::current() === null) {
            return $this->missingProfile();
        }

        return view('admin.career-history.create', ['entry' => new CareerHistory]);
    }

    public function store(StoreCareerHistoryRequest $request): RedirectResponse
    {
        $profile = JournalistProfile::current();

        if ($profile === null) {
            return $this->missingProfile();
        }

        $profile->careerHistory()->create([
            ...$request->validated(),
            'sort_order' => ($profile->careerHistory()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.career-history.index')->with('status', 'Career entry added.');
    }

    public function edit(CareerHistory $careerHistory): View
    {
        return view('admin.career-history.edit', ['entry' => $careerHistory]);
    }

    public function update(UpdateCareerHistoryRequest $request, CareerHistory $careerHistory): RedirectResponse
    {
        $careerHistory->update($request->validated());

        return redirect()->route('admin.career-history.index')->with('status', 'Career entry updated.');
    }

    public function destroy(CareerHistory $careerHistory): RedirectResponse
    {
        $careerHistory->delete();

        return redirect()->route('admin.career-history.index')->with('status', 'Career entry removed.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->applyOrder(CareerHistory::class, $request->validated('ids'));

        return $this->reordered();
    }

    private function missingProfile(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')->withErrors([
            'profile' => 'Set up the journalist profile before adding career history.',
        ]);
    }
}
