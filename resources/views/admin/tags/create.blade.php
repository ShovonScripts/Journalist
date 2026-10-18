@extends('admin.layout')

@section('title', 'New tag')

@section('content')
    <form method="POST" action="{{ route('admin.tags.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.tags._form', ['tag' => $tag])

        <x-admin.form-actions :cancel="route('admin.tags.index')" label="Create tag" />
    </form>
@endsection
