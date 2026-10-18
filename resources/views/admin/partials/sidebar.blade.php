<aside class="border-hairline lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:border-r">
    {{-- Desktop --}}
    <div class="hidden h-full flex-col lg:flex">
        @include('admin.partials.sidebar-brand')
        @include('admin.partials.nav')
    </div>

    {{-- Mobile drawer --}}
    <div x-cloak x-show="navOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Admin navigation">
        <div
            x-show="navOpen"
            x-transition.opacity.duration.150ms
            class="absolute inset-0 bg-ink/40"
            @click="navOpen = false"
        ></div>

        <div
            x-show="navOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            class="relative flex h-full w-72 max-w-[85%] flex-col border-r border-hairline bg-paper"
            @keydown.escape.window="navOpen = false"
        >
            @include('admin.partials.sidebar-brand')
            @include('admin.partials.nav')
        </div>
    </div>
</aside>
