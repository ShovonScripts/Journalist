@extends('admin.layout')

@section('title', 'Edit media')

@section('content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="grid place-items-center rounded border border-hairline bg-white p-6">
            @if ($media->type === \App\Enums\MediaType::Image)
                <img src="{{ $media->url() }}" alt="{{ $media->alt_text }}" class="max-h-[60vh] rounded">
            @else
                <p class="text-[13px] text-muted">{{ $media->original_filename }}</p>
            @endif
        </div>

        <aside class="space-y-6">
            <form method="POST" action="{{ route('admin.media.update', $media) }}" class="space-y-5 rounded border border-hairline bg-white p-5">
                @csrf
                @method('PUT')

                <div>
                    <p class="truncate text-[13px] font-medium">{{ $media->original_filename }}</p>
                    <p class="mt-1 text-[12px] text-muted">{{ $media->type->label() }} · uploaded {{ $media->created_at?->timezone('Asia/Dhaka')->format('Y-m-d') }}</p>
                </div>

                <x-admin.field.text name="alt_text" label="Alt text" :value="$media->alt_text" hint="Required before this image can appear on published work." />
                <x-admin.field.textarea name="caption" label="Caption" :value="$media->caption" :rows="3" />

                <x-admin.form-actions :cancel="route('admin.media.index')" label="Save" />
            </form>

            <div class="rounded border border-hairline bg-white p-5 text-[13px]">
                <p class="text-[12px] font-semibold uppercase tracking-wider text-muted">In use by</p>

                @if ($references === [])
                    <p class="mt-2 text-muted">Not referenced anywhere.</p>
                    <div class="mt-4">
                        <x-admin.confirm
                            :action="route('admin.media.destroy', $media)"
                            :message="'Delete “'.$media->original_filename.'”? This cannot be undone.'"
                        />
                    </div>
                @else
                    <ul class="mt-2 list-inside list-disc text-muted">
                        @foreach ($references as $reference)
                            <li>{{ $reference }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-[12px] text-muted">Unset it there before deleting.</p>
                @endif
            </div>
        </aside>
    </div>
@endsection
