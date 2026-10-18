@extends('admin.layout')

@section('title', 'Edit tag')

@section('content')
    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.tags._form', ['tag' => $tag])

        <x-admin.form-actions :cancel="route('admin.tags.index')" label="Save changes" />
    </form>
@endsection
