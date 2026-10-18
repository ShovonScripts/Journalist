@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-medium">Overview</h2>
                <p class="mt-1 text-[13px] text-muted">
                    Phase 1 shell. Content counts and quick actions arrive with the CMS core.
                </p>
            </div>

            <span class="cursor-not-allowed rounded border border-dashed border-hairline px-3 py-1.5 text-[12px] text-muted" title="Available once the content screens ship">
                New content — soon
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach (['Draft' => 'draft', 'Scheduled' => 'scheduled', 'Published' => 'published'] as $label => $key)
                <div class="rounded border border-hairline bg-white p-5">
                    <p class="text-[12px] uppercase tracking-wider text-muted">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-light tabular-nums">{{ $counts[$key] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded border border-hairline bg-white p-5">
                <h3 class="text-[12px] font-medium uppercase tracking-wider text-muted">Recently published</h3>
                <p class="mt-3 text-[13px] text-muted">Nothing yet.</p>
            </section>

            <section class="rounded border border-hairline bg-white p-5">
                <h3 class="text-[12px] font-medium uppercase tracking-wider text-muted">Upcoming scheduled</h3>
                <p class="mt-3 text-[13px] text-muted">Nothing scheduled.</p>
            </section>
        </div>

        <section class="rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-medium uppercase tracking-wider text-muted">Unread messages</h3>
            <p class="mt-2 text-2xl font-light tabular-nums">{{ $unreadMessages }}</p>
        </section>
    </div>
@endsection
