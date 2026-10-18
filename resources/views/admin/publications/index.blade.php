@extends('admin.layout')

@section('title', 'Publications')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Publications',
        'count' => $publications->count(),
        'noun' => 'publication',
        'create' => route('admin.publications.create'),
    ])

    @if ($publications->isEmpty())
        <x-admin.empty-state
            title="No publications yet"
            message="Publications are the outlets work has appeared in — The Daily Star is just one row like any other."
            :action="route('admin.publications.create')"
            action-label="New publication"
        />
    @else
        <x-admin.data-table
            :reorder="route('admin.publications.reorder')"
            :columns="[
                ['key' => 'move', 'label' => ''],
                ['key' => 'name', 'label' => 'Outlet'],
                ['key' => 'slug', 'label' => 'Slug'],
                ['key' => 'content', 'label' => 'Content'],
                ['key' => 'actions', 'label' => ''],
            ]"
        >
            @foreach ($publications as $publication)
                <tr class="hover:bg-ink/[0.02]" data-reorder-item data-id="{{ $publication->id }}">
                    <td class="w-8 px-2 py-3"><x-admin.reorder-handle /></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if ($publication->logo)
                                <img src="{{ $publication->logo->url() }}" alt="" class="h-8 w-8 rounded object-contain">
                            @endif
                            <a href="{{ route('admin.publications.edit', $publication) }}" class="font-medium hover:text-accent">
                                {{ $publication->name }}
                            </a>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $publication->slug }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $publication->content_items_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-admin.confirm
                            :action="route('admin.publications.destroy', $publication)"
                            :message="'Delete the “'.$publication->name.'” outlet? This cannot be undone.'"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.data-table>
    @endif
@endsection
