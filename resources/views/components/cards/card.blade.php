@props([
    'title',
    'route' => '#',
    'icon' => 'heroicon-o-clipboard-document-check',
    'description' => null,
    'count' => null,
])

<a href="{{ $route }}" class="block bg-white dark:bg-neutral-800 p-5 rounded-xl shadow hover:shadow-lg transition group border border-neutral-200 dark:border-neutral-700">
    <div class="flex items-center gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-200 transition">
                @svg($icon, 'w-6 h-6')
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $title }}</h3>
            @if ($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</p>
            @endif
        </div>
        @if (!is_null($count))
            <div>
                <span class="inline-block bg-indigo-600 text-white text-sm px-2 py-1 rounded-full font-medium">
                    {{ $count }}
                </span>
            </div>
        @endif
    </div>
</a>
