@extends('admin.layout')

@section('title', 'New award')

@section('content')
    <form method="POST" action="{{ route('admin.awards.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.awards._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.awards.index')" label="Add award" />
    </form>
@endsection
