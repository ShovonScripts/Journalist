@extends('admin.layout')

@section('title', 'New education entry')

@section('content')
    <form method="POST" action="{{ route('admin.education.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.education._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.education.index')" label="Add entry" />
    </form>
@endsection
