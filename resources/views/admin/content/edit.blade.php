@extends('admin.layout')

@section('title', 'Edit content')

@section('content')
    <form
        method="POST"
        action="{{ route('admin.content.update', $item) }}"
        x-data="contentForm({ sourceType: '{{ old('source_type', $item->source_type?->value) }}', contentType: '{{ old('content_type', $item->content_type?->value) }}' })"
        @input="dirty = true"
        @change="dirty = true"
        @submit="saved()"
    >
        @csrf
        @method('PUT')

        @include('admin.content._form', ['item' => $item])

        <div class="mt-8">
            <x-admin.form-actions :cancel="route('admin.content.index')" label="Save changes" />
        </div>
    </form>
@endsection
