@extends('admin.layout')

@section('title', 'Edit category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.categories._form', ['category' => $category])

        <x-admin.form-actions :cancel="route('admin.categories.index')" label="Save changes" />
    </form>
@endsection
