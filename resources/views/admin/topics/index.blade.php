@extends('admin.layout')

@section('title', 'Topics')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Topics',
        'count' => $topics->count(),
        'noun' => 'topic',
        'create' => route('admin.topics.create'),
    ])

    @if ($topics->isEmpty())
        <x-admin.empty-state
            title="No topics yet"
            message="Topics are the curated beat dossiers that power the subject-based pages."
            :action="route('admin.topics.create')"
            action-label="New topic"
        />
    @else
        <x-admin.data-table :columns="[
            ['key' => 'name', 'label' => 'Topic'],
            ['key' => 'slug', 'label' => 'Slug'],
            ['key' => 'content', 'label' => 'Content'],
            ['key' => 'order', 'label' => 'Order'],
            ['key' => 'actions', 'label' => ''],
        ]">
            @foreach ($topics as $topic)
                <tr class="hover:bg-ink/[0.02]">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if ($topic->featuredImage)
                                <img src="{{ $topic->featuredImage->url() }}" alt="" class="h-9 w-12 rounded object-cover">
                            @endif
                            <a href="{{ route('admin.topics.edit', $topic) }}" class="font-medium hover:text-accent">{{ $topic->name }}</a>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $topic->slug }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $topic->content_items_count }}</td>
                    <td class="px-4 py-3 tabular-nums text-muted">{{ $topic->sort_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-admin.confirm
                            :action="route('admin.topics.destroy', $topic)"
                            :message="'Delete the “'.$topic->name.'” topic? This cannot be undone.'"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.data-table>
    @endif
@endsection
