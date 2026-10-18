<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReordersRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\StoreEducationRequest;
use App\Http\Requests\Admin\UpdateEducationRequest;
use App\Models\Education;
use App\Models\JournalistProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Degrees and study (ADMIN_PANEL.md §12), attached to the singleton profile.
 */
class EducationController extends Controller
{
    use ReordersRecords;

    public function index(): View
    {
        $profile = JournalistProfile::current();

        return view('admin.education.index', [
            'profile' => $profile,
            'entries' => $profile?->education()->get() ?? collect(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (JournalistProfile::current() === null) {
            return $this->missingProfile();
        }

        return view('admin.education.create', ['entry' => new Education]);
    }

    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $profile = JournalistProfile::current();

        if ($profile === null) {
            return $this->missingProfile();
        }

        $profile->education()->create([
            ...$request->validated(),
            'sort_order' => ($profile->education()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.education.index')->with('status', 'Education entry added.');
    }

    public function edit(Education $education): View
    {
        return view('admin.education.edit', ['entry' => $education]);
    }

    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        return redirect()->route('admin.education.index')->with('status', 'Education entry updated.');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        return redirect()->route('admin.education.index')->with('status', 'Education entry removed.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->applyOrder(Education::class, $request->validated('ids'));

        return $this->reordered();
    }

    private function missingProfile(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')->withErrors([
            'profile' => 'Set up the journalist profile before adding education.',
        ]);
    }
}
