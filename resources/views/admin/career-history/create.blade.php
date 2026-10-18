@extends('admin.layout')

@section('title', 'New career entry')

@section('content')
    <form method="POST" action="{{ route('admin.career-history.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.career-history._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.career-history.index')" label="Add entry" />
    </form>
@endsection
