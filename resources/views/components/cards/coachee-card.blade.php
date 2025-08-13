{{-- resources/views/components/cards/coachee-card.blade.php --}}
@props(['coachee'])

<div class="group relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700
            transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
            shadow-[0_2px_8px_-1px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_-2px_rgba(0,0,0,0.12)]
            hover:-translate-y-1 overflow-hidden coachee-list-card"
     style="--tw-shadow-colored: 0 8px 24px -2px var(--tw-shadow-color);">

    {{-- Efecto de iluminación al hacer hover --}}
    <div class="absolute inset-0 bg-gradient-to-br from-primary-50/20 to-transparent dark:from-primary-900/10
                opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
    </div>

    <div class="p-6 relative z-10">
        {{-- Avatar --}}
        <div class="flex justify-center mb-4">
            <div class="relative">
                <div class="absolute inset-0 rounded-full bg-primary-500/10 scale-90 group-hover:scale-100 
                          blur-md transition-all duration-500"></div>
                <flux:profile
                    circle
                    :chevron="false"
                    :avatar="$coachee->profile_photo_url"
                    size="lg"
                    class="relative z-10 ring-2 ring-white dark:ring-gray-800 group-hover:ring-primary-200 dark:group-hover:ring-primary-500 transition-all"
                />
            </div>
        </div>

        {{-- Nombre y email --}}
        <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {{ $coachee->name }}
        </h3>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
            {{ $coachee->email }}
        </p>

        {{-- Separador con efecto --}}
        <div class="relative py-4">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200 dark:border-gray-700 group-hover:border-primary-200 dark:group-hover:border-primary-500 transition-colors"></div>
            </div>
        </div>

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 group-hover:bg-primary-50/30 dark:group-hover:bg-primary-900/10 transition-colors">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-300 transition-colors">
                    Evaluaciones
                </p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-700 dark:group-hover:text-primary-200 transition-colors">
                    {{ $coachee->evaluations_count ?? 0 }}
                </p>
            </div>
            
            <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 group-hover:bg-primary-50/30 dark:group-hover:bg-primary-900/10 transition-colors">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-300 transition-colors">
                    Promedio
                </p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-700 dark:group-hover:text-primary-200 transition-colors">
                    {{ number_format($coachee->average_score ?? 0, 1) }}
                </p>
            </div>
        </div>
         {{-- Slot para acciones --}}
        <div class="flex justify-center my-4">
            {{ $slot }}
        </div>
    </div>
</div>