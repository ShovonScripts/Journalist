@extends('admin.layout')

@section('title', 'Edit topic')

@section('content')
    <form method="POST" action="{{ route('admin.topics.update', $topic) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.topics._form', ['topic' => $topic])

        <x-admin.form-actions :cancel="route('admin.topics.index')" label="Save changes" />
    </form>
@endsection
