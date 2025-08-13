@props(['session'])

<div
  class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-[0_2px_8px_-1px_rgba(0,0,0,0.04)] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:-translate-y-1 hover:shadow-[0_8px_24px_-2px_rgba(0,0,0,0.12)] dark:border-gray-700 dark:bg-gray-800">

  <div
    class="from-primary-50/20 dark:from-primary-900/10 pointer-events-none absolute inset-0 bg-gradient-to-br to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
  </div>

  <div class="relative z-10 space-y-4">
    {{-- Header --}}
    <div class="flex items-center gap-4">
      <div class="relative">
        <div
          class="bg-primary-500/10 absolute inset-0 scale-90 rounded-full blur-md transition-all duration-500 group-hover:scale-100">
        </div>
        <flux:profile :avatar="$session->participant->profile_photo_url" :chevron="false" circle
          class="group-hover:ring-primary-200 dark:group-hover:ring-primary-500 relative z-10 ring-2 ring-white transition-all dark:ring-gray-800"
          size="lg" />
      </div>

      <div class="flex-1">
        <h3
          class="group-hover:text-primary-600 dark:group-hover:text-primary-400 text-lg font-semibold text-gray-800 transition-colors dark:text-gray-100">
          {{ $session->participant->name }}
        </h3>
        <p
          class="text-sm text-gray-500 transition-colors group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-gray-300">
          {{ $session->participant->email }}
        </p>
      </div>
    </div>

    {{-- Progreso usando tus métodos del modelo --}}
    @php
      // Usando los accessors que definiste en el modelo
      $plannedItems = $session->planned_items_count; // accessor
      $answeredItems = $session->answered_items_count; // accessor
      $progressPercent = (int) max(0, min(100, $session->progress_percent ?? 0)); // accessor
    @endphp

    <div>
      {{-- Estado usando el accessor status_name --}}
      <div class="py-3">
        <flux:badge color="{{ $session->status_pill_color }}" variant="pill">
          #{{ $session->id }} {{ $session->status_name }}
        </flux:badge>
      </div>
      <p
        class="group-hover:text-primary-600 dark:group-hover:text-primary-300 mb-1 text-xs font-medium text-gray-600 transition-colors dark:text-gray-400">
        Progreso: {{ $answeredItems }} / {{ $plannedItems }} ({{ $progressPercent }}%)
      </p>

      <div class="relative h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
        <div class="bg-primary-500 absolute inset-y-0 left-0 rounded-full transition-all duration-500"
          style="width: {{ $progressPercent }}%"></div>
      </div>
    </div>

    {{-- Fechas --}}
    <flux:text>Fechas</flux:text>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div
        class="group-hover:bg-primary-50/30 dark:group-hover:bg-primary-900/10 rounded-lg bg-gray-50 p-2 transition-colors dark:bg-gray-700/50">
        <p
          class="group-hover:text-primary-600 dark:group-hover:text-primary-300 text-xs text-gray-500 transition-colors dark:text-gray-400">
          {{ __('Inicio') }}
        </p>
        <p
          class="group-hover:text-primary-700 dark:group-hover:text-primary-200 text-sm font-medium text-gray-800 transition-colors dark:text-gray-100">
          {{ $session->created_at?->format('d/m/Y') ?? '—' }}
        </p>
      </div>
      <div
        class="group-hover:bg-primary-50/30 dark:group-hover:bg-primary-900/10 rounded-lg bg-gray-50 p-2 transition-colors dark:bg-gray-700/50">
        <p
          class="group-hover:text-primary-600 dark:group-hover:text-primary-300 text-xs text-gray-500 transition-colors dark:text-gray-400">
          {{ __('Actualización') }}
        </p>
        <p
          class="group-hover:text-primary-700 dark:group-hover:text-primary-200 text-sm font-medium text-gray-800 transition-colors dark:text-gray-100">
          {{ $session->updated_at?->format('d/m/Y') ?? '—' }}
        </p>
      </div>
    </div>

    {{-- Estado usando métodos del modelo --}}
    @if ($session->isCompleted())
      <div class="rounded-lg border border-green-200 bg-green-50 p-2 dark:border-green-800 dark:bg-green-900/20">
        <p class="flex items-center justify-center gap-1 text-sm text-blue-800 dark:text-blue-200">
          {{ __('general.statuses.completed') }}
          <x-heroicon-c-check-circle class="h-4 w-4" />
        </p>
      </div>
    @elseif($session->isSigned())
      <div class="rounded-lg border border-blue-200 bg-blue-50 p-2 dark:border-blue-800 dark:bg-blue-900/20">
        <p class="flex items-center justify-center gap-1 text-sm text-blue-800 dark:text-blue-200">
          {{ __('general.statuses.signed') }}
          <x-heroicon-c-check-circle class="h-4 w-4" />
        </p>
      </div>
    @endif

    {{-- Firmas usando relaciones --}}
    @php $signedCount = $session->signed_signatures_count ?? $session->signatures->where('status','signed')->count(); @endphp

    <div
      class="group-hover:bg-primary-50/30 dark:group-hover:bg-primary-900/10 rounded-lg bg-gray-50 p-2 transition-colors dark:bg-gray-700/50">
      <p
        class="group-hover:text-primary-600 dark:group-hover:text-primary-300 text-xs text-gray-500 transition-colors dark:text-gray-400">
        {{ __('general.signatures') }} ({{ $signedCount }} de 2)
      </p>
      @if ($signedCount >= 2)
        <span
          class="mt-1 inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-200">
          {{ __('general.signed') }}
        </span>
      @else
        <span
          class="mt-1 inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200">
          {{ __('general.pending_signatures') }}
        </span>
      @endif
    </div>

    {{-- Verificar si necesita firma del usuario actual (ejemplo) --}}
    @auth
      @if ($session->needsSignatureFrom(auth()->id(), 'coachee'))
        <div class="rounded-lg border border-orange-200 bg-orange-50 p-2 dark:border-orange-800 dark:bg-orange-900/20">
          <p class="text-sm text-orange-800 dark:text-orange-200">
            ⚠️ Requiere firma de {{ $session->participant->name }}
          </p>
        </div>
      @endif
    @endauth

    {{-- Información adicional usando relaciones --}}
    <div class="grid grid-cols-2 gap-4 text-xs">
      <div>
        <span class="text-gray-500 dark:text-gray-400">Ciclo</span>
        <span class="font-medium">{{ $session->cycle ?? 'N/A' }}</span>
      </div>
      <div>
        <span class="text-gray-500 dark:text-gray-400">{{ __('general.division') }}</span>
        <span class="font-medium">{{ $session->division->name ?? 'N/A' }}</span>
      </div>
    </div>

    {{-- Acciones --}}
    <div class="flex justify-end pt-2">
      <div class="opacity-90 transition-opacity duration-300 group-hover:opacity-100">
        {{ $slot }}
      </div>
    </div>
  </div>
</div>
