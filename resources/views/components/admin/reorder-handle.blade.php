@props([])

{{--
    The drag grip for a reorderable row (ADMIN_PANEL.md §3). Only the handle is
    draggable, so a row's own links stay clickable.
--}}
<button
    type="button"
    draggable="true"
    data-reorder-handle
    aria-label="Drag to reorder"
    title="Drag to reorder"
    {{ $attributes->merge(['class' => 'cursor-move rounded px-1.5 py-1 text-muted hover:bg-ink/5 hover:text-ink']) }}
>
    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5" fill="currentColor" aria-hidden="true">
        <circle cx="5" cy="4" r="1.4" />
        <circle cx="11" cy="4" r="1.4" />
        <circle cx="5" cy="8" r="1.4" />
        <circle cx="11" cy="8" r="1.4" />
        <circle cx="5" cy="12" r="1.4" />
        <circle cx="11" cy="12" r="1.4" />
    </svg>
</button>
