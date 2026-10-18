@props(['name', 'label' => null, 'hint' => null, 'value' => null, 'required' => false])

{{--
    The rich-text editor mounts here in a later phase (Tiptap bundle). The
    editor is UX, not a security boundary — the server sanitizer runs on save
    regardless of what the client sends (ADMIN_PANEL.md §2, §7). Until then
    this is a plain textarea that posts to the same field.
--}}
<x-admin.field.textarea
    :name="$name"
    :label="$label"
    :hint="$hint"
    :value="$value"
    :required="$required"
    rows="14"
    {{ $attributes->merge(['class' => 'font-mono text-[13px]']) }}
/>
