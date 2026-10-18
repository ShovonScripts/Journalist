<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }} Admin</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="h-full bg-paper font-sans text-ink antialiased" x-data="{ navOpen: false }">
    <div class="min-h-full lg:grid lg:grid-cols-[16rem_1fr]">
        @include('admin.partials.sidebar')

        <div class="flex min-h-full flex-col">
            @include('admin.partials.topbar')

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                @include('admin.partials.flash')
                @include('admin.partials.errors')

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
