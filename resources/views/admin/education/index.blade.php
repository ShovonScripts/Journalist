@extends('admin.layout')

@section('title', 'Education')

@section('content')
    @include('admin.partials.registry-header', [
        'heading' => 'Education',
        'count' => $entries->count(),
        'noun' => 'entry',
        'create' => $profile ? route('admin.education.create') : null,
    ])

    @if ($profile === null)
        <x-admin.empty-state
            title="No journalist profile yet"
            message="The profile is the anchor for career entries, education and awards."
        />
    @elseif ($entries->isEmpty())
        <x-admin.empty-state
            title="No education entries yet"
            message="Add institutions, programs and dates for the About page."
            :action="route('admin.education.create')"
            action-label="New entry"
        />
    @else
        <ul data-reorder="{{ route('admin.education.reorder') }}"
            class="divide-y divide-hairline overflow-hidden rounded border border-hairline bg-white">
            @foreach ($entries as $entry)
                <li data-reorder-item data-id="{{ $entry->id }}" class="flex items-start gap-3 px-4 py-3 hover:bg-ink/[0.02]">
                    <div class="pt-0.5"><x-admin.reorder-handle /></div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium">{{ $entry->institution }}</p>
                        <p class="text-[12px] text-muted">
                            {{ $entry->program ?: '—' }}
                            @if ($entry->start_date || $entry->end_date)
                                · {{ $entry->start_date?->format('M Y') ?? '—' }} – {{ $entry->end_date?->format('M Y') ?? 'Present' }}
                            @endif
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 pt-0.5 text-[12px]">
                        <a href="{{ route('admin.education.edit', $entry) }}" class="text-accent hover:underline">Edit</a>
                        <x-admin.confirm
                            :action="route('admin.education.destroy', $entry)"
                            :message="'Remove the “'.$entry->institution.'” entry? This cannot be undone.'"
                        />
                    </div>
                </li>
            @endforeach
        </ul>

        <p class="mt-3 text-[12px] text-muted">Drag a row by its handle to reorder.</p>
    @endif
@endsection
