@extends('admin.layout')

@section('title', 'Tags')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Tags',
        'count' => $tags->count(),
        'noun' => 'tag',
        'create' => route('admin.tags.create'),
    ])

    @if ($tags->isEmpty())
        <x-admin.empty-state
            title="No tags yet"
            message="Tags are the free-form, fine-grained axis readers use to discover related work."
            :action="route('admin.tags.create')"
            action-label="New tag"
        />
    @else
        <x-admin.data-table :columns="[
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'slug', 'label' => 'Slug'],
            ['key' => 'content', 'label' => 'Content'],
            ['key' => 'actions', 'label' => ''],
        ]">
            @foreach ($tags as $tag)
                <tr class="hover:bg-ink/[0.02]">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.tags.edit', $tag) }}" class="font-medium hover:text-accent">{{ $tag->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $tag->slug }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $tag->content_items_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-admin.confirm
                            :action="route('admin.tags.destroy', $tag)"
                            :message="'Delete the “'.$tag->name.'” tag? This cannot be undone.'"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.data-table>
    @endif
@endsection
