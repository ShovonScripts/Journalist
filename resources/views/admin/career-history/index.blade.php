@extends('admin.layout')

@section('title', 'Career history')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Career history',
        'count' => $entries->count(),
        'noun' => 'entry',
        'create' => $profile ? route('admin.career-history.create') : null,
    ])

    @if ($profile === null)
        <x-admin.empty-state
            title="No journalist profile yet"
            message="The profile is the anchor for career entries, education and awards."
        />
    @elseif ($entries->isEmpty())
        <x-admin.empty-state
            title="No career entries yet"
            message="Add roles and organisations to build the timeline shown on the About page."
            :action="route('admin.career-history.create')"
            action-label="New entry"
        />
    @else
        <ul data-reorder="{{ route('admin.career-history.reorder') }}"
            class="divide-y divide-hairline overflow-hidden rounded border border-hairline bg-white">
            @foreach ($entries as $entry)
                <li data-reorder-item data-id="{{ $entry->id }}" class="flex items-start gap-3 px-4 py-3 hover:bg-ink/[0.02]">
                    <div class="pt-0.5"><x-admin.reorder-handle /></div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium">{{ $entry->role }}</p>
                        <p class="text-[12px] text-muted">
                            {{ $entry->organization }}
                            @if ($entry->start_date || $entry->end_date)
                                · {{ $entry->start_date?->format('M Y') ?? '—' }} – {{ $entry->end_date?->format('M Y') ?? 'Present' }}
                            @endif
                        </p>
                        @if ($entry->description)
                            <p class="mt-1 text-[12px] leading-relaxed text-muted">{{ $entry->description }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-3 pt-0.5 text-[12px]">
                        <a href="{{ route('admin.career-history.edit', $entry) }}" class="text-accent hover:underline">Edit</a>
                        <x-admin.confirm
                            :action="route('admin.career-history.destroy', $entry)"
                            :message="'Remove the “'.$entry->role.'” entry? This cannot be undone.'"
                        />
                    </div>
                </li>
            @endforeach
        </ul>

        <p class="mt-3 text-[12px] text-muted">Drag a row by its handle to reorder the timeline.</p>
    @endif
@endsection
