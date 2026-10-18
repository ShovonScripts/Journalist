@props(['columns' => [], 'sort' => null, 'direction' => 'asc'])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded border border-hairline bg-white']) }}>
    <table class="min-w-full text-[13px]">
        <thead>
            <tr class="border-b border-hairline text-left text-[11px] uppercase tracking-wider text-muted">
                @foreach ($columns as $column)
                    <th class="whitespace-nowrap px-4 py-3 font-medium">
                        @if (! empty($column['sort']))
                            @php($active = $sort === $column['key'])
                            <a
                                href="{{ request()->fullUrlWithQuery(['sort' => $column['key'], 'direction' => $active && $direction === 'asc' ? 'desc' : 'asc', 'page' => null]) }}"
                                class="inline-flex items-center gap-1 hover:text-ink"
                            >
                                {{ $column['label'] }}
                                @if ($active)
                                    <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        @else
                            {{ $column['label'] }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-hairline">
            {{ $slot }}
        </tbody>
    </table>
</div>
