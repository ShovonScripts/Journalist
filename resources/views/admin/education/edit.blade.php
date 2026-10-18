@extends('admin.layout')

@section('title', 'Edit education entry')

@section('content')
    <form method="POST" action="{{ route('admin.education.update', $entry) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.education._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.education.index')" label="Save changes" />
    </form>
@endsection
