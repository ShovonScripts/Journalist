@extends('admin.layout')

@section('title', 'New publication')

@section('content')
    <form method="POST" action="{{ route('admin.publications.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('admin.publications._form', ['publication' => $publication])

        <x-admin.form-actions :cancel="route('admin.publications.index')" label="Create publication" />
    </form>
@endsection
