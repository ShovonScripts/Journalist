<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ config('app.name') }} Admin</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-paper px-4 font-sans text-ink antialiased">
    <main class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <p class="text-[11px] uppercase tracking-[0.2em] text-muted">Administration</p>
            <h1 class="mt-2 font-serif text-2xl">Sign in</h1>
        </div>

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="passcode" class="block text-[13px] font-medium">Passcode</label>
                <input
                    id="passcode"
                    name="passcode"
                    type="password"
                    autocomplete="current-password"
                    autofocus
                    required
                    class="mt-1 block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                >
                @include('admin.partials.field-error', ['for' => 'passcode'])
            </div>

            <button
                type="submit"
                class="w-full rounded bg-accent px-4 py-2 text-sm font-medium text-paper transition-colors hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-paper"
            >
                Sign in
            </button>
        </form>

        <p class="mt-6 text-center text-[12px] text-muted">
            Single-owner access. No account recovery by email.
        </p>
    </main>
</body>
</html>
