@props(['cancel' => null, 'label' => 'Save', 'cancelLabel' => 'Cancel'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
    <button type="submit" class="rounded bg-accent px-4 py-2 text-[13px] font-medium text-paper hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-paper">
        {{ $label }}
    </button>

    @if ($cancel)
        <a href="{{ $cancel }}" class="rounded border border-hairline px-4 py-2 text-[13px] hover:bg-ink/5">
            {{ $cancelLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
