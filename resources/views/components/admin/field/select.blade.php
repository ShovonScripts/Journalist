@props(['name', 'label' => null, 'hint' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false])

<label class="block">
    @if ($label)
        <span class="mb-1 block text-[13px] font-medium">
            {{ $label }}@if ($required)<span class="text-accent" aria-hidden="true"> *</span>@endif
        </span>
    @endif

    <select
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent']) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <span class="mt-1 block text-[12px] text-muted">{{ $hint }}</span>
    @endif

    @error($name)
        <p class="mt-1 text-[12px] text-accent">{{ $message }}</p>
    @enderror
</label>
