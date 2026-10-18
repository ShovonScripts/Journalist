@extends('admin.layout')

@section('title', 'Content')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'All content',
        'count' => $items->total(),
        'noun' => 'piece',
        'create' => route('admin.content.create'),
    ])

    <x-admin.filter-bar class="mb-6">
        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Search</span>
            <input name="q" value="{{ request('q') }}" placeholder="Title or summary…" class="w-56 rounded border border-hairline bg-white px-3 py-2 text-[13px] outline-none focus:border-accent">
        </label>

        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Status</span>
            <select name="status" class="rounded border border-hairline bg-white px-3 py-2 text-[13px]">
                <option value="">Any</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Type</span>
            <select name="type" class="rounded border border-hairline bg-white px-3 py-2 text-[13px]">
                <option value="">Any</option>
                @foreach ($contentTypes as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Source</span>
            <select name="source" class="rounded border border-hairline bg-white px-3 py-2 text-[13px]">
                <option value="">Any</option>
                @foreach ($sourceTypes as $source)
                    <option value="{{ $source->value }}" @selected(request('source') === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Category</span>
            <select name="category" class="rounded border border-hairline bg-white px-3 py-2 text-[13px]">
                <option value="">Any</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-1 block text-[12px] text-muted">Topic</span>
            <select name="topic" class="rounded border border-hairline bg-white px-3 py-2 text-[13px]">
                <option value="">Any</option>
                @foreach ($topics as $topic)
                    <option value="{{ $topic->id }}" @selected((int) request('topic') === $topic->id)>{{ $topic->name }}</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="rounded border border-hairline px-4 py-2 text-[13px] hover:bg-ink/5">Filter</button>

        @if ($hasFilters)
            <a href="{{ route('admin.content.index') }}" class="py-2 text-[13px] text-accent hover:underline">Clear</a>
        @endif
    </x-admin.filter-bar>

    @if ($items->isEmpty())
        @if ($hasFilters)
            <x-admin.empty-state title="Nothing matches these filters" message="Try a broader search or clear the filters." :action="route('admin.content.index')" action-label="Clear filters" />
        @else
            <x-admin.empty-state title="No content yet" message="Create your first piece — internal article or external work." :action="route('admin.content.create')" action-label="New content" />
        @endif
    @else
        <form method="POST" action="{{ route('admin.content.bulk') }}" x-data="{ ids: [], confirm: false }">
            @csrf

            <div class="mb-4 flex flex-wrap items-center gap-3 text-[13px]">
                <select name="action" class="rounded border border-hairline bg-white px-3 py-2">
                    <option value="publish">Publish</option>
                    <option value="unpublish">Unpublish</option>
                    <option value="delete">Delete</option>
                </select>

                <button type="button" @click="if (ids.length) confirm = true" class="rounded border border-hairline px-4 py-2 hover:bg-ink/5">
                    Apply to <span x-text="ids.length"></span> selected
                </button>

                <span class="text-muted" x-show="ids.length === 0" x-cloak>Select rows to act on them.</span>

                <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                    <div class="absolute inset-0 bg-ink/40" @click="confirm = false"></div>
                    <div class="relative w-full max-w-sm rounded border border-hairline bg-paper p-6">
                        <p class="text-sm font-medium">Apply this action?</p>
                        <p class="mt-2 text-[13px] text-muted">
                            This will apply to <span x-text="ids.length"></span> selected pieces.
                        </p>
                        <div class="mt-5 flex justify-end gap-3 text-[13px]">
                            <button type="button" @click="confirm = false" class="rounded border border-hairline px-3 py-1.5 hover:bg-ink/5">Cancel</button>
                            <button type="submit" class="rounded bg-accent px-3 py-1.5 font-medium text-paper hover:bg-accent/90">Apply</button>
                        </div>
                    </div>
                </div>
            </div>

            <x-admin.data-table
                :sort="$sort"
                :direction="$direction"
                :columns="[
                    ['key' => 'select', 'label' => ''],
                    ['key' => 'published_at', 'label' => 'Date', 'sort' => true],
                    ['key' => 'title', 'label' => 'Title', 'sort' => true],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'sort' => true],
                    ['key' => 'actions', 'label' => ''],
                ]"
            >
                @foreach ($items as $item)
                    @php
                        $statusTone = match ($item->status) {
                            \App\Enums\ContentStatus::Published => 'success',
                            \App\Enums\ContentStatus::Scheduled => 'warning',
                            default => 'muted',
                        };
                    @endphp
                    <tr class="hover:bg-ink/[0.02]">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" x-model="ids" class="h-4 w-4 rounded border-hairline text-accent focus:ring-accent">
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-muted">
                            {{ ($item->published_at ?? $item->created_at)?->timezone('Asia/Dhaka')->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.content.edit', $item) }}" class="font-medium hover:text-accent">{{ $item->title }}</a>
                        </td>
                        <td class="px-4 py-3"><x-admin.badge tone="ink">{{ $item->content_type->label() }}</x-admin.badge></td>
                        <td class="px-4 py-3">
                            @if ($item->isExternal())
                                <x-admin.badge tone="accent">{{ $item->publication?->name ?? 'External' }}</x-admin.badge>
                            @else
                                <x-admin.badge>On-site</x-admin.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-admin.badge :tone="$statusTone">{{ $item->status->label() }}</x-admin.badge></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <x-admin.confirm
                                :action="route('admin.content.destroy', $item)"
                                :message="'Delete “'.$item->title.'”? This cannot be undone.'"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </form>
    @endif
@endsection
