@extends('admin.layout')

@section('title', 'Edit award')

@section('content')
    <form method="POST" action="{{ route('admin.awards.update', $entry) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.awards._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.awards.index')" label="Save changes" />
    </form>
@endsection
