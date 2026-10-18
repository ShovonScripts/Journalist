@props(['tone' => 'muted'])

@php
    $tones = [
        'muted' => 'border-hairline text-muted',
        'ink' => 'border-ink/20 text-ink',
        'accent' => 'border-accent/40 text-accent',
        'success' => 'border-emerald-600/40 text-emerald-700',
        'warning' => 'border-amber-600/40 text-amber-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] leading-none '.($tones[$tone] ?? $tones['muted'])]) }}>
    {{ $slot }}
</span>
