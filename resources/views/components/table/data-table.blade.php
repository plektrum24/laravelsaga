@props([
    'headers' => [],
    'rows' => [],
    'striped' => true,
])

@php
    $tableClasses = 'w-full text-left text-sm';
@endphp

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if($headers)
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3.5 font-semibold text-gray-800 dark:text-white/90">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        
        <tbody>
            @forelse($rows as $row)
                <tr class="@if($striped && $loop->odd) bg-gray-50 dark:bg-white/[0.02] @endif border-b border-gray-100 last:border-0 dark:border-gray-800">
                    @foreach($row as $cell)
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No data available
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
