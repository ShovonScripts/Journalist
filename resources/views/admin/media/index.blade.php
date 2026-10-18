@extends('admin.layout')

@section('title', 'Media library')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-lg font-medium">Media library</h2>
            <p class="mt-1 text-[13px] text-muted">{{ $media->total() }} files</p>
        </div>

        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="flex items-end gap-3">
            @csrf
            <label class="block">
                <span class="mb-1 block text-[12px] text-muted">Upload</span>
                <input type="file" name="file" required accept="image/*,application/pdf" class="text-[12px]">
            </label>
            <button type="submit" class="rounded bg-accent px-4 py-2 text-[13px] font-medium text-paper hover:bg-accent/90">Upload</button>
        </form>
    </div>

    <div class="mb-6 flex gap-2 text-[13px]">
        @foreach (['' => 'All', 'images' => 'Images', 'missing-alt' => 'Missing alt text'] as $key => $label)
            <a
                href="{{ route('admin.media.index', array_filter(['filter' => $key])) }}"
                @class([
                    'rounded-full border px-3 py-1',
                    'border-accent text-accent' => $filter === $key,
                    'border-hairline text-muted hover:text-ink' => $filter !== $key,
                ])
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($media->isEmpty())
        <x-admin.empty-state title="No media yet" message="Upload images and documents here, then attach them from any content form." />
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($media as $file)
                <a href="{{ route('admin.media.edit', $file) }}" class="group overflow-hidden rounded border border-hairline bg-white">
                    <div class="grid h-36 place-items-center bg-ink/5">
                        @if ($file->type === \App\Enums\MediaType::Image)
                            <img src="{{ $file->url() }}" alt="{{ $file->alt_text }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-[11px] uppercase tracking-wider text-muted">{{ $file->type->label() }}</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <p class="truncate text-[12px] font-medium group-hover:text-accent">{{ $file->original_filename }}</p>
                        <p class="mt-1 flex items-center gap-2 text-[11px] text-muted">
                            <span>{{ $file->type->label() }}</span>
                            @if ($file->type === \App\Enums\MediaType::Image && blank($file->alt_text))
                                <x-admin.badge tone="accent">Needs alt text</x-admin.badge>
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $media->links() }}</div>
    @endif
@endsection
