@if ($errors->any())
    <div class="mb-6 rounded border border-accent/40 bg-accent/5 px-4 py-3 text-[13px] text-accent" role="alert">
        <ul class="space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
