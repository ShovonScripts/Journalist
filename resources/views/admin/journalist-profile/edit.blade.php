@extends('admin.layout')

@section('title', 'Journalist profile')

@section('content')
    <div class="mb-6 max-w-3xl">
        <h2 class="text-lg font-medium">Journalist profile</h2>
        <p class="mt-1 text-[13px] text-muted">
            Your public identity. There is one profile — this screen edits it; it is never created or duplicated.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.journalist-profile.update', $profile) }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <fieldset class="space-y-5 rounded border border-hairline bg-white p-5">
            <legend class="px-1 text-[12px] font-semibold uppercase tracking-wider text-muted">Identity</legend>

            <x-admin.field.text name="name" label="Name" :value="$profile->name" required />
            <x-admin.field.text name="title" label="Job title" :value="$profile->title" />
            <x-admin.media-picker
                name="photo_media_id"
                label="Portrait"
                :value="$profile->photo_media_id"
                :preview="$profile->photo?->url()"
                hint="Used on the About page and in article bylines."
            />
        </fieldset>

        <fieldset class="space-y-5 rounded border border-hairline bg-white p-5">
            <legend class="px-1 text-[12px] font-semibold uppercase tracking-wider text-muted">Biography</legend>

            <x-admin.field.textarea name="short_bio" label="Short bio" :value="$profile->short_bio" :rows="2" hint="One or two lines. Shown in compact contexts." />
            <x-admin.field.textarea name="long_bio" label="Long bio" :value="$profile->long_bio" :rows="8" />
            <x-admin.field.text
                name="beats"
                label="Beats"
                :value="$beats"
                hint="Comma-separated, e.g. Politics, Human rights, Climate."
            />
        </fieldset>

        <fieldset class="space-y-5 rounded border border-hairline bg-white p-5">
            <legend class="px-1 text-[12px] font-semibold uppercase tracking-wider text-muted">Social links</legend>

            @foreach (\App\Models\JournalistProfile::SOCIAL_PLATFORMS as $platform)
                <x-admin.field.text
                    :name="'social['.$platform.']'"
                    :old="'social.'.$platform"
                    :label="$platform === 'x' ? 'X (Twitter)' : ucfirst($platform)"
                    :type="$platform === 'email' ? 'email' : 'url'"
                    :value="$social[$platform] ?? ''"
                />
            @endforeach
        </fieldset>

        <fieldset class="space-y-3 rounded border border-hairline bg-white p-5">
            <legend class="px-1 text-[12px] font-semibold uppercase tracking-wider text-muted">Publications</legend>

            @if ($publications->isEmpty())
                <p class="text-[13px] text-muted">
                    No outlets yet — <a href="{{ route('admin.publications.create') }}" class="text-accent hover:underline">add one</a>.
                </p>
            @else
                @php($selected = collect(old('publications', $profile->publications->modelKeys()))->map(fn ($id) => (int) $id))
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($publications as $publication)
                        <label class="flex items-center gap-2 text-[13px]">
                            <input
                                type="checkbox"
                                name="publications[]"
                                value="{{ $publication->id }}"
                                @checked($selected->contains($publication->id))
                                class="h-4 w-4 rounded border-hairline text-accent focus:ring-accent"
                            >
                            {{ $publication->name }}
                        </label>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <x-admin.form-actions :cancel="route('admin.dashboard')" label="Save profile" />
    </form>
@endsection
