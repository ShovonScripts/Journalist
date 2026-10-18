@extends('admin.layout')

@section('title', 'My profile')

@section('content')
    <div class="mb-6">
        <h2 class="text-lg font-medium">My profile</h2>
        <p class="mt-1 text-[13px] text-muted">Your sign-in account. This is separate from the public journalist profile.</p>
    </div>

    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5 rounded border border-hairline bg-white p-5">
            @csrf
            @method('PUT')

            <p class="text-[12px] font-semibold uppercase tracking-wider text-muted">Account</p>

            <x-admin.field.text name="name" label="Name" :value="$user->name" required />
            <x-admin.field.text name="email" label="Email" type="email" :value="$user->email" required />

            <x-admin.form-actions label="Save account" />
        </form>

        <form method="POST" action="{{ route('admin.profile.passcode') }}" class="space-y-5 rounded border border-hairline bg-white p-5">
            @csrf
            @method('PUT')

            <p class="text-[12px] font-semibold uppercase tracking-wider text-muted">Passcode</p>

            <x-admin.field.text name="current_passcode" label="Current passcode" type="password" required />
            <x-admin.field.text name="passcode" label="New passcode" type="password" required />
            <x-admin.field.text name="passcode_confirmation" label="Confirm new passcode" type="password" required />

            <x-admin.form-actions label="Change passcode" />
        </form>
    </div>
@endsection
