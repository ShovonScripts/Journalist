@props(['action', 'method' => 'DELETE', 'message', 'label' => 'Delete', 'title' => 'Please confirm'])

<span x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" {{ $attributes->merge(['class' => 'text-accent hover:underline']) }}>
        {{ $label }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-ink/40" @click="open = false" x-transition.opacity.duration.150ms></div>

        <div class="relative w-full max-w-sm rounded border border-hairline bg-paper p-6" x-transition>
            <p class="text-sm font-medium">{{ $title }}</p>
            <p class="mt-2 text-[13px] leading-relaxed text-muted">{{ $message }}</p>

            <div class="mt-5 flex justify-end gap-3 text-[13px]">
                <button type="button" @click="open = false" class="rounded border border-hairline px-3 py-1.5 hover:bg-ink/5">
                    Cancel
                </button>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <button type="submit" class="rounded bg-accent px-3 py-1.5 font-medium text-paper hover:bg-accent/90">
                        {{ $label }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</span>
