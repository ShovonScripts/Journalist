@extends('admin.layout')

@section('title', 'New topic')

@section('content')
    <form method="POST" action="{{ route('admin.topics.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.topics._form', ['topic' => $topic])

        <x-admin.form-actions :cancel="route('admin.topics.index')" label="Create topic" />
    </form>
@endsection
