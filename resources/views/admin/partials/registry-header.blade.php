{{--
    Shared header for registry/index screens: a count line on the left and the
    primary "New …" action on the right (ADMIN_PANEL.md §10).
--}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-lg font-medium">{{ $heading }}</h2>
        <p class="mt-1 text-[13px] text-muted">
            {{ $count }} {{ \Illuminate\Support\Str::plural($noun, $count) }}
        </p>
    </div>

    @isset($create)
        <a href="{{ $create }}" class="rounded bg-accent px-4 py-2 text-[13px] font-medium text-paper hover:bg-accent/90">
            New {{ $noun }}
        </a>
    @endisset
</div>
