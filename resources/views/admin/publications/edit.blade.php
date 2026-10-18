@extends('admin.layout')

@section('title', 'Edit publication')

@section('content')
    <form method="POST" action="{{ route('admin.publications.update', $publication) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('admin.publications._form', ['publication' => $publication])

        <x-admin.form-actions :cancel="route('admin.publications.index')" label="Save changes" />
    </form>
@endsection
