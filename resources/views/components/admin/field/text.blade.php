@props(['name', 'label' => null, 'hint' => null, 'value' => null, 'type' => 'text', 'required' => false, 'old' => null])

@php($key = $old ?? $name)

<label class="block">
    @if ($label)
        <span class="mb-1 block text-[13px] font-medium">
            {{ $label }}@if ($required)<span class="text-accent" aria-hidden="true"> *</span>@endif
        </span>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($key, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent']) }}
    >

    @if ($hint)
        <span class="mt-1 block text-[12px] text-muted">{{ $hint }}</span>
    @endif

    @error($key)
        <p class="mt-1 text-[12px] text-accent">{{ $message }}</p>
    @enderror
</label>
