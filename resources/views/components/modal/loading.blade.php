@props([
    'title' => 'Loading',
])

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70">
    <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col items-center gap-4">
            <!-- Spinner -->
            <div class="relative h-12 w-12">
                <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-gray-700"></div>
                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-brand-500 animate-spin"></div>
            </div>
            
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $title }}
            </p>
        </div>
    </div>
</div>
