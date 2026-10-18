<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::query()->withCount('contentItems')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tags.create', ['tag' => new Tag]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::query()->create($this->prepare($request->validated(), null));

        return redirect()->route('admin.tags.index')->with('status', 'Tag created.');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', ['tag' => $tag]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($this->prepare($request->validated(), $tag->id));

        return redirect()->route('admin.tags.index')->with('status', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        if ($tag->contentItems()->exists()) {
            return back()->withErrors([
                'tag' => 'This tag is in use. Merge or remove it from its content first.',
            ]);
        }

        $tag->delete();

        return redirect()->route('admin.tags.index')->with('status', 'Tag deleted.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?int $ignoreId): array
    {
        $data['slug'] = Slug::uniqueFor(Tag::class, ($data['slug'] ?? null) ?: $data['name'], $ignoreId);

        return $data;
    }
}
