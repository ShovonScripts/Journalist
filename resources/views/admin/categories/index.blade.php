@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Categories',
        'count' => $categories->count(),
        'noun' => 'category',
        'create' => route('admin.categories.create'),
    ])

    @if ($categories->isEmpty())
        <x-admin.empty-state
            title="No categories yet"
            message="Categories are the filing sections readers browse the archive by."
            :action="route('admin.categories.create')"
            action-label="New category"
        />
    @else
        <x-admin.data-table
            :reorder="route('admin.categories.reorder')"
            :columns="[
                ['key' => 'move', 'label' => ''],
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'slug', 'label' => 'Slug'],
                ['key' => 'content', 'label' => 'Content'],
                ['key' => 'order', 'label' => 'Order'],
                ['key' => 'actions', 'label' => ''],
            ]"
        >
            @foreach ($categories as $category)
                <tr class="hover:bg-ink/[0.02]" data-reorder-item data-id="{{ $category->id }}">
                    <td class="w-8 px-2 py-3"><x-admin.reorder-handle /></td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium hover:text-accent">
                            {{ $category->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $category->slug }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $category->content_items_count }}</td>
                    <td class="px-4 py-3 tabular-nums text-muted">{{ $category->sort_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-admin.confirm
                            :action="route('admin.categories.destroy', $category)"
                            :message="'Delete the “'.$category->name.'” category? This cannot be undone.'"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.data-table>
    @endif
@endsection
