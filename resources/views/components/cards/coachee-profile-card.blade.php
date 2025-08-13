@props(['coachee'])

<div class="group relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700
            transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
            shadow-[0_2px_8px_-1px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_-2px_rgba(0,0,0,0.12)]
            hover:-translate-y-1 overflow-hidden coachee-list-card">

    {{-- Iluminación en hover --}}
    <div class="absolute inset-0 bg-gradient-to-br from-primary-50/20 to-transparent dark:from-primary-900/10
                opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

    <div class="p-6 relative z-10 flex flex-col items-center">
        {{-- Avatar con halo --}}
        <div class="relative mb-4">
            <span class="absolute inset-0 rounded-full bg-primary-400/20 dark:bg-primary-700/30 scale-110 blur-xl group-hover:scale-125 transition-transform duration-500"></span>
            <flux:profile
                circle
                :chevron="false"
                :avatar="$coachee->profile_photo_url"
                size="lg"
                class="relative z-10 ring-2 ring-white dark:ring-gray-800 group-hover:ring-primary-200 dark:group-hover:ring-primary-500 transition-all"
            />
        </div>
        {{-- Nombre y email --}}
        <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {{ $coachee->name }}
        </h3>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
            {{ $coachee->email }}
        </p>
    </div>
</div>
