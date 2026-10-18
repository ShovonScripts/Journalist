@extends('admin.layout')

@section('title', 'Edit career entry')

@section('content')
    <form method="POST" action="{{ route('admin.career-history.update', $entry) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.career-history._form', ['entry' => $entry])

        <x-admin.form-actions :cancel="route('admin.career-history.index')" label="Save changes" />
    </form>
@endsection
