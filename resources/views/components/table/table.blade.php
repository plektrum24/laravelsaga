@props([
    'headers' => [],
    'striped' => true,
])

@php
    $tableClasses = 'w-full text-left text-sm';
    $rowClasses = $striped ? 'odd:bg-gray-50 dark:odd:bg-white/[0.02]' : '';
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
            {{ $slot }}
        </tbody>
    </table>
</div>
