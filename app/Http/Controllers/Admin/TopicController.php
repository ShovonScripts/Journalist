<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReordersRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\StoreTopicRequest;
use App\Http\Requests\Admin\UpdateTopicRequest;
use App\Models\Topic;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TopicController extends Controller
{
    use ReordersRecords;

    public function index(): View
    {
        return view('admin.topics.index', [
            'topics' => Topic::query()
                ->with('featuredImage')
                ->withCount('contentItems')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.topics.create', ['topic' => new Topic]);
    }

    public function store(StoreTopicRequest $request): RedirectResponse
    {
        Topic::query()->create($this->prepare($request->validated(), null));

        return redirect()->route('admin.topics.index')->with('status', 'Topic created.');
    }

    public function edit(Topic $topic): View
    {
        return view('admin.topics.edit', ['topic' => $topic]);
    }

    public function update(UpdateTopicRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update($this->prepare($request->validated(), $topic->id));

        return redirect()->route('admin.topics.index')->with('status', 'Topic updated.');
    }

    public function destroy(Topic $topic): RedirectResponse
    {
        if ($topic->contentItems()->exists()) {
            return back()->withErrors([
                'topic' => 'This topic is in use. Unassign it from its content first.',
            ]);
        }

        $topic->delete();

        return redirect()->route('admin.topics.index')->with('status', 'Topic deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->applyOrder(Topic::class, $request->validated('ids'));

        return $this->reordered();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?int $ignoreId): array
    {
        $data['slug'] = Slug::uniqueFor(Topic::class, ($data['slug'] ?? null) ?: $data['name'], $ignoreId);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
