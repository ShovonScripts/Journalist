@props(['method' => 'GET'])

<form method="{{ $method }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-end gap-3']) }}>
    {{ $slot }}
</form>
