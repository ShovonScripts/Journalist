@props(['name', 'label', 'value' => '1', 'checked' => false, 'hint' => null])

<label class="flex items-start gap-2 text-[13px]">
    <input type="hidden" name="{{ $name }}" value="0">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-hairline text-accent focus:ring-accent']) }}
    >
    <span>
        {{ $label }}
        @if ($hint)
            <span class="block text-[12px] text-muted">{{ $hint }}</span>
        @endif
    </span>
</label>
