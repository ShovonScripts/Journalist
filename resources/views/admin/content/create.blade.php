@extends('admin.layout')

@section('title', 'New content')

@section('content')
    <form
        method="POST"
        action="{{ route('admin.content.store') }}"
        x-data="contentForm({ sourceType: '{{ old('source_type', 'internal') }}', contentType: '{{ old('content_type', 'news') }}' })"
        @input="dirty = true"
        @change="dirty = true"
        @submit="saved()"
    >
        @csrf

        @include('admin.content._form', ['item' => $item])

        <div class="mt-8">
            <x-admin.form-actions :cancel="route('admin.content.index')" label="Create content" />
        </div>
    </form>
@endsection
