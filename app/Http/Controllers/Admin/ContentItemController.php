<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkContentActionRequest;
use App\Http\Requests\Admin\StoreContentItemRequest;
use App\Http\Requests\Admin\UpdateContentItemRequest;
use App\Models\Category;
use App\Models\ContentItem;
use App\Models\Publication;
use App\Models\Tag;
use App\Models\Topic;
use App\Support\HtmlSanitizer;
use App\Support\ReadingTime;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * One controller, one form partial and one validation class for all seven
 * content types — conditional fields, not parallel screens
 * (ADMIN_PANEL.md §5, §7).
 */
class ContentItemController extends Controller
{
    private const SORTABLE = ['title', 'status', 'published_at', 'created_at', 'updated_at'];

    public function index(Request $request): View
    {
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $items = ContentItem::query()
            ->with(['category', 'publication'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')))
            ->when($request->filled('source'), fn ($query) => $query->where('source_type', $request->string('source')))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('publication'), fn ($query) => $query->where('publication_id', $request->integer('publication')))
            ->when($request->filled('topic'), fn ($query) => $query->whereHas('topics', fn ($sub) => $sub->where('topics.id', $request->integer('topic'))))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($sub) => $sub->where('title', 'like', $term)->orWhere('summary', 'like', $term));
            })
            ->orderBy($sort, $direction)
            ->paginate(25)
            ->withQueryString();

        if ($items->isEmpty() && $items->currentPage() > 1) {
            abort(404);
        }

        return view('admin.content.index', [
            'items' => $items,
            'sort' => $sort,
            'direction' => $direction,
            'categories' => Category::query()->orderBy('name')->get(),
            'publications' => Publication::query()->orderBy('name')->get(),
            'topics' => Topic::query()->orderBy('name')->get(),
            'contentTypes' => ContentType::cases(),
            'statuses' => ContentStatus::cases(),
            'sourceTypes' => SourceType::cases(),
            'hasFilters' => $request->hasAny(['status', 'type', 'source', 'category', 'publication', 'topic', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.content.create', $this->formData(new ContentItem));
    }

    public function store(StoreContentItemRequest $request): RedirectResponse
    {
        $data = $this->prepare($request->validated(), null);
        $data['author_id'] = $request->user()->id;

        $item = ContentItem::query()->create($data);
        $this->syncRelations($item, $request);

        return redirect()->route('admin.content.edit', $item)->with('status', 'Content saved.');
    }

    public function edit(ContentItem $contentItem): View
    {
        $contentItem->load(['tags', 'topics', 'related', 'featuredImage', 'seoOgImage']);

        return view('admin.content.edit', $this->formData($contentItem));
    }

    public function update(UpdateContentItemRequest $request, ContentItem $contentItem): RedirectResponse
    {
        $contentItem->update($this->prepare($request->validated(), $contentItem));
        $this->syncRelations($contentItem, $request);

        return redirect()->route('admin.content.edit', $contentItem)->with('status', 'Content saved.');
    }

    public function destroy(ContentItem $contentItem): RedirectResponse
    {
        $contentItem->delete();

        return redirect()->route('admin.content.index')->with('status', 'Content deleted.');
    }

    public function bulk(BulkContentActionRequest $request): RedirectResponse
    {
        $items = ContentItem::query()->whereIn('id', $request->input('ids'))->get();
        $action = $request->string('action')->toString();

        foreach ($items as $item) {
            match ($action) {
                'publish' => $item->update([
                    'status' => ContentStatus::Published,
                    'published_at' => $item->published_at ?? now(),
                ]),
                'unpublish' => $item->update(['status' => ContentStatus::Draft]),
                'delete' => $item->delete(),
            };
        }

        $verb = ['publish' => 'Published', 'unpublish' => 'Unpublished', 'delete' => 'Deleted'][$action];

        return back()->with('status', $verb.' '.$items->count().' item'.($items->count() === 1 ? '' : 's').'.');
    }

    public function slugCheck(Request $request): JsonResponse
    {
        $slug = $request->string('slug')->toString();
        $ignore = $request->integer('ignore') ?: null;

        return response()->json([
            'slug' => Slug::make($slug),
            'available' => Slug::isAvailable(ContentItem::class, $slug, $ignore),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(ContentItem $item): array
    {
        return [
            'item' => $item,
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'publications' => Publication::query()->orderBy('name')->pluck('name', 'id'),
            'tags' => Tag::query()->orderBy('name')->get(),
            'topics' => Topic::query()->orderBy('name')->get(),
            'allContent' => ContentItem::query()
                ->when($item->exists, fn ($query) => $query->whereKeyNot($item->id))
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'title']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?ContentItem $existing): array
    {
        $type = ContentType::from($data['content_type']);
        $source = SourceType::from($data['source_type']);

        $data['body'] = $source === SourceType::External
            ? null
            : HtmlSanitizer::clean($data['body'] ?? '');

        $data['interviewee_name'] = $type === ContentType::Interview ? ($data['interviewee_name'] ?? null) : null;
        $data['interviewee_title'] = $type === ContentType::Interview ? ($data['interviewee_title'] ?? null) : null;
        $data['video_url'] = $type === ContentType::Video ? ($data['video_url'] ?? null) : null;

        if ($source === SourceType::Internal) {
            $data['external_url'] = null;
            $data['publication_id'] = null;
        }

        $data['slug'] = Slug::uniqueFor(ContentItem::class, ($data['slug'] ?? null) ?: $data['title'], $existing?->id);

        $status = ContentStatus::from($data['status']);
        $publishedAt = $data['published_at'] ?? null;

        if ($status === ContentStatus::Published) {
            if (blank($publishedAt)) {
                $publishedAt = now();
            } elseif (Carbon::parse($publishedAt)->isFuture()) {
                $status = ContentStatus::Scheduled;
            }
        }

        $data['status'] = $status;
        $data['published_at'] = $publishedAt ? Carbon::parse($publishedAt) : null;

        if ($source === SourceType::External) {
            $data['reading_time_minutes'] = null;
        } elseif (blank($data['reading_time_minutes'] ?? null)) {
            $data['reading_time_minutes'] = ReadingTime::minutesFromHtml($data['body']);
        }

        return $data;
    }

    private function syncRelations(ContentItem $item, Request $request): void
    {
        $item->tags()->sync($request->input('tags', []));
        $item->topics()->sync($request->input('topics', []));
        $item->related()->sync(
            collect($request->input('related', []))->reject(fn ($id) => (int) $id === $item->id)->all(),
        );
    }
}
