@props(['name', 'label' => 'Image', 'value' => null, 'preview' => null, 'hint' => null])

<div
    x-data="mediaPicker({
        endpoint: '{{ route('admin.media.pick') }}',
        upload: '{{ route('admin.media.store') }}',
        csrf: '{{ csrf_token() }}',
        initial: {{ $value !== null ? (int) $value : 'null' }},
        preview: @js($preview),
    })"
    class="space-y-2"
>
    <span class="block text-[13px] font-medium">{{ $label }}</span>

    <input type="hidden" name="{{ $name }}" :value="selected ?? ''">

    <div class="flex items-center gap-4">
        <div class="grid h-20 w-28 place-items-center overflow-hidden rounded border border-hairline bg-ink/5">
            <template x-if="previewUrl">
                <img :src="previewUrl" alt="" class="h-full w-full object-cover">
            </template>
            <template x-if="! previewUrl">
                <span class="text-[11px] text-muted">No image</span>
            </template>
        </div>

        <div class="flex gap-2 text-[13px]">
            <button type="button" @click="open = true; load()" class="rounded border border-hairline px-3 py-1.5 hover:bg-ink/5">
                Choose
            </button>
            <button type="button" x-show="selected" x-cloak @click="clear()" class="rounded border border-hairline px-3 py-1.5 text-muted hover:bg-ink/5">
                Remove
            </button>
        </div>
    </div>

    @if ($hint)
        <span class="block text-[12px] text-muted">{{ $hint }}</span>
    @endif

    @error($name)
        <p class="text-[12px] text-accent">{{ $message }}</p>
    @enderror

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-ink/40" @click="open = false"></div>

        <div class="relative flex max-h-[80vh] w-full max-w-2xl flex-col rounded border border-hairline bg-paper" @keydown.escape.window="open = false">
            <header class="flex items-center gap-3 border-b border-hairline p-4">
                <input
                    type="search"
                    x-model.debounce.300ms="term"
                    @input="load()"
                    placeholder="Search media…"
                    class="w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                >
                <button type="button" @click="open = false" class="text-[13px] text-muted hover:text-ink">Close</button>
            </header>

            <div class="grid flex-1 grid-cols-3 gap-3 overflow-y-auto p-4 sm:grid-cols-4">
                <template x-for="item in items" :key="item.id">
                    <button type="button" @click="choose(item)" class="overflow-hidden rounded border border-hairline bg-white text-left hover:border-accent">
                        <img :src="item.url" :alt="item.alt || ''" class="h-24 w-full object-cover">
                        <span class="block truncate p-2 text-[11px] text-muted" x-text="item.filename"></span>
                    </button>
                </template>

                <p x-show="! items.length" x-cloak class="col-span-full py-8 text-center text-[13px] text-muted">
                    No media found.
                </p>
            </div>

            <footer class="flex items-center gap-3 border-t border-hairline p-4">
                <input type="file" x-ref="uploadInput" accept="image/*,application/pdf" class="text-[12px]">
                <button type="button" @click="uploadFile()" class="ml-auto rounded bg-accent px-3 py-1.5 text-[13px] font-medium text-paper hover:bg-accent/90">
                    Upload
                </button>
            </footer>
        </div>
    </div>
</div>
