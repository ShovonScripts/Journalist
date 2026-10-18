@props(['name', 'label' => null, 'hint' => null, 'value' => null, 'required' => false])

@php
    $current = old($name, $value);

    if ($current instanceof \DateTimeInterface) {
        $current = $current->format('Y-m-d\TH:i');
    } elseif (is_string($current) && $current !== '') {
        $current = \Illuminate\Support\Carbon::parse($current)->format('Y-m-d\TH:i');
    }
@endphp

<label class="block">
    @if ($label)
        <span class="mb-1 block text-[13px] font-medium">
            {{ $label }}@if ($required)<span class="text-accent" aria-hidden="true"> *</span>@endif
        </span>
    @endif

    <input
        type="datetime-local"
        name="{{ $name }}"
        value="{{ $current }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent']) }}
    >

    @if ($hint)
        <span class="mt-1 block text-[12px] text-muted">{{ $hint }}</span>
    @endif

    @error($name)
        <p class="mt-1 text-[12px] text-accent">{{ $message }}</p>
    @enderror
</label>
