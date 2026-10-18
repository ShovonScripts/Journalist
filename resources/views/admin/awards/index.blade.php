@extends('admin.layout')

@section('title', 'Awards')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Awards',
        'count' => $entries->count(),
        'noun' => 'award',
        'create' => $profile ? route('admin.awards.create') : null,
    ])

    @if ($profile === null)
        <x-admin.empty-state
            title="No journalist profile yet"
            message="The profile is the anchor for career entries, education and awards."
        />
    @elseif ($entries->isEmpty())
        <x-admin.empty-state
            title="No awards yet"
            message="Record prizes and recognition, with an optional certificate image."
            :action="route('admin.awards.create')"
            action-label="New award"
        />
    @else
        <ul data-reorder="{{ route('admin.awards.reorder') }}"
            class="divide-y divide-hairline overflow-hidden rounded border border-hairline bg-white">
            @foreach ($entries as $entry)
                <li data-reorder-item data-id="{{ $entry->id }}" class="flex items-start gap-3 px-4 py-3 hover:bg-ink/[0.02]">
                    <div class="pt-0.5"><x-admin.reorder-handle /></div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium">{{ $entry->title }}</p>
                        <p class="text-[12px] text-muted">
                            {{ $entry->awarding_body ?: '—' }}@if ($entry->year) · {{ $entry->year }}@endif
                        </p>
                        @if ($entry->description)
                            <p class="mt-1 text-[12px] leading-relaxed text-muted">{{ $entry->description }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-3 pt-0.5 text-[12px]">
                        <a href="{{ route('admin.awards.edit', $entry) }}" class="text-accent hover:underline">Edit</a>
                        <x-admin.confirm
                            :action="route('admin.awards.destroy', $entry)"
                            :message="'Remove the “'.$entry->title.'” award? This cannot be undone.'"
                        />
                    </div>
                </li>
            @endforeach
        </ul>

        <p class="mt-3 text-[12px] text-muted">Drag a row by its handle to reorder.</p>
    @endif
@endsection
