@extends('admin.layout')

@section('title', 'New category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.categories._form', ['category' => $category])

        <x-admin.form-actions :cancel="route('admin.categories.index')" label="Create category" />
    </form>
@endsection
