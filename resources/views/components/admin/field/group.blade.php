@props(['legend' => null, 'when' => 'true'])

{{--
    Conditionally-shown group of fields. The Alpine `x-show` is convenience only:
    every rule is enforced again server-side in the FormRequest, because a
    hand-crafted POST never ran the JavaScript (ADMIN_PANEL.md §7).
--}}
<fieldset x-show="{{ $when }}" x-cloak class="space-y-4 rounded border border-hairline bg-white p-5">
    @if ($legend)
        <legend class="px-1 text-[12px] font-semibold uppercase tracking-wider text-muted">{{ $legend }}</legend>
    @endif

    {{ $slot }}
</fieldset>
