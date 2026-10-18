<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePublicationRequest;
use App\Http\Requests\Admin\UpdatePublicationRequest;
use App\Models\Publication;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(): View
    {
        return view('admin.publications.index', [
            'publications' => Publication::query()
                ->with('logo')
                ->withCount('contentItems')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.publications.create', ['publication' => new Publication]);
    }

    public function store(StorePublicationRequest $request): RedirectResponse
    {
        Publication::query()->create($this->prepare($request->validated(), null));

        return redirect()->route('admin.publications.index')->with('status', 'Publication created.');
    }

    public function edit(Publication $publication): View
    {
        return view('admin.publications.edit', ['publication' => $publication]);
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): RedirectResponse
    {
        $publication->update($this->prepare($request->validated(), $publication->id));

        return redirect()->route('admin.publications.index')->with('status', 'Publication updated.');
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        if ($publication->contentItems()->exists()) {
            return back()->withErrors([
                'publication' => 'This outlet is referenced by published work. Reassign that work first.',
            ]);
        }

        $publication->delete();

        return redirect()->route('admin.publications.index')->with('status', 'Publication deleted.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?int $ignoreId): array
    {
        $data['slug'] = Slug::uniqueFor(Publication::class, ($data['slug'] ?? null) ?: $data['name'], $ignoreId);

        return $data;
    }
}
