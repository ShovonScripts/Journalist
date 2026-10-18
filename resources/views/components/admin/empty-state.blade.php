@props(['title', 'message' => null, 'action' => null, 'actionLabel' => 'Create'])

<div {{ $attributes->merge(['class' => 'rounded border border-dashed border-hairline bg-white px-6 py-12 text-center']) }}>
    <p class="text-sm font-medium">{{ $title }}</p>

    @if ($message)
        <p class="mx-auto mt-1 max-w-sm text-[13px] text-muted">{{ $message }}</p>
    @endif

    @if ($action)
        <a href="{{ $action }}" class="mt-4 inline-block rounded bg-accent px-4 py-2 text-[13px] font-medium text-paper hover:bg-accent/90">
            {{ $actionLabel }}
        </a>
    @endif
</div>
