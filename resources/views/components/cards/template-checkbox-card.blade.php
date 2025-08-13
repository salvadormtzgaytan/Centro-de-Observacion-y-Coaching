@props(['template', 'checked' => false])

<label
    class="hover:border-primary-500 @if ($checked) ring-2 ring-primary-500 @endif flex cursor-pointer items-start gap-4 rounded-lg border border-gray-300 bg-white p-4 transition-colors dark:border-gray-600 dark:bg-gray-800">

    <flux:icon.numbered-list />
    <div class="flex-1">
        <span class="block font-semibold text-gray-900 dark:text-white">
            {{ $template->name }}
        </span>
        <div class="mb-1 flex items-center gap-2">
            {{-- Ícono de canal --}}
            @php
                $channelIcon = match ($template->channel?->key ?? '') {
                    'farmacia' => 'shopping-bag',
                    'hospital' => 'building-office',
                    'consultorio' => 'building-storefront', // Cambiado aquí
                    'ventas_campo' => 'globe',
                    'otc' => 'document-duplicate',
                    default => 'list-bullet',
                };

            @endphp
            <flux:icon :name="$channelIcon" class="h-5 w-5 text-blue-500" />
            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $template->channel?->name ?? '-' }}</span>

            {{-- Ícono de nivel --}}
            @php
                $levelIcon = match ($template->level?->key ?? '') {
                    'basico' => 'circle-stack',
                    'intermedio' => 'adjustments-horizontal',
                    'avanzado' => 'arrow-trending-up',
                    default => 'star',
                };

            @endphp
            <flux:icon :name="$levelIcon" class="ml-4 h-5 w-5 text-yellow-500" />
            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $template->level?->name ?? '-' }}</span>
        </div>
        {{-- Secciones y preguntas --}}
        <span class="block text-sm text-gray-500 dark:text-gray-400">
            <flux:badge color="purple" size="sm">
                Secciones {{ $template->sections ? count($template->sections) : 0 }}
            </flux:badge>
            <flux:badge color="blue" size="sm">
                Preguntas {{ $template->sections ? $template->sections->flatMap->items->count() : 0 }}
            </flux:badge>
        </span>
        {{-- Estado y fecha --}}
        <span class="mt-1 block text-xs text-gray-400">
            <flux:badge color="{{ $template->status === 'published' ? 'green' : 'gray' }}" size="xs">
                {{ __($template->status === 'published' ? 'Publicada' : 'Borrador') }}
            </flux:badge>
            &middot;
            Actualizada {{ $template->updated_at->diffForHumans() }}
        </span>
    </div>

    <flux:checkbox name="templates[]" wire:model="templates_selected" value="{{ $template->id }}"  :checked="$checked" />
</label>
