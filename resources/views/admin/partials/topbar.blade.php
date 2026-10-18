<header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-hairline bg-paper/90 px-4 backdrop-blur sm:px-6 lg:px-10">
    <button
        type="button"
        class="-ml-2 rounded p-2 text-muted hover:bg-ink/5 hover:text-ink lg:hidden"
        @click="navOpen = true"
        aria-label="Open navigation"
    >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round" />
        </svg>
    </button>

    <h1 class="text-sm font-medium tracking-tight">@yield('title', 'Dashboard')</h1>

    <div class="ms-auto flex items-center gap-4 text-[13px]">
        <span class="hidden text-muted sm:inline">{{ auth()->user()?->name }}</span>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="rounded px-1 text-muted transition-colors hover:text-accent">
                Sign out
            </button>
        </form>
    </div>
</header>
