@if (session('status'))
    <div class="mb-6 rounded border border-hairline bg-white px-4 py-3 text-[13px] text-ink" role="status">
        {{ session('status') }}
    </div>
@endif
