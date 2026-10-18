@php
    use App\Support\AdminNavigation;
@endphp

<nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4 text-[13px]">
    @foreach (AdminNavigation::groups() as $group)
        <div>
            @isset($group['label'])
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-muted">
                    {{ $group['label'] }}
                </p>
            @endisset

            <ul class="space-y-0.5">
                @foreach ($group['items'] as $item)
                    @php
                        $available = AdminNavigation::isAvailable($item);
                        $active = AdminNavigation::isActive($item);
                    @endphp

                    <li>
                        @if ($available)
                            <a
                                href="{{ AdminNavigation::url($item) }}"
                                @class([
                                    'block rounded px-3 py-1.5 transition-colors',
                                    'bg-ink/5 font-medium text-ink' => $active,
                                    'text-muted hover:bg-ink/5 hover:text-ink' => ! $active,
                                ])
                            >
                                {{ $item['label'] }}
                            </a>
                        @else
                            <span class="flex items-center justify-between rounded px-3 py-1.5 text-muted/60" title="Not yet built">
                                {{ $item['label'] }}
                                <span class="text-[10px] uppercase tracking-wide">soon</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
