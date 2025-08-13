<div class="co-page">
  <h1 class="co-title">
    {{ __('Mis Evaluaciones') }}
  </h1>

  {{-- Filtros + Export --}}
  <div class="co-section mt-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">

      {{-- Estado --}}
      <div data-flux-field>
        <label class="co-label" data-flux-label>Estado</label>
        <flux:select class="co-input" wire:model.defer="filters.status">
          <flux:select.option value="">Todos</flux:select.option>
          @foreach ($statuses as $val => $label)
            <flux:select.option value="{{ $val }}">{{ $label }}</flux:select.option>
          @endforeach
        </flux:select>
      </div>

      {{-- Ciclo --}}
      <div data-flux-field>
        <label class="co-label" data-flux-label>Ciclo</label>
        <flux:select class="co-input" wire:model.defer="filters.cycle">
          <flux:select.option value="">Todos</flux:select.option>
          @foreach ($cycles as $c)
            <flux:select.option value="{{ $c }}">{{ $c }}</flux:select.option>
          @endforeach
        </flux:select>
      </div>

      {{-- Fecha --}}
      <div data-flux-field>
        <label class="co-label" data-flux-label>Fecha desde / hasta</label>
        <div class="mt-1 flex flex-wrap gap-2">
          <flux:input class="co-input flex-1" type="date" wire:model.defer="filters.from" />
          <flux:input class="co-input flex-1" type="date" wire:model.defer="filters.to" />
        </div>
      </div>

      {{-- Botones --}}
      <div class="flex flex-wrap items-end gap-2 sm:col-span-2">
        <flux:button class="w-full sm:w-auto" variant="primary" wire:click.prevent="$refresh">Filtrar</flux:button>
        <flux:button class="w-full sm:w-auto" variant="outline" wire:click.prevent="resetFilters">Limpiar</flux:button>
        <flux:button class="w-full sm:w-auto" color="orange" icon="arrow-down-tray" variant="primary"
          wire:click.prevent="exportToExcel">
          Exportar a Excel
        </flux:button>
      </div>
    </div>
  </div>

  {{-- Tabla --}}
  <div class="co-table-shell mt-4">
    <table class="co-table">
      <thead>
        <tr>
          <th class="w-16">ID</th>
          <th>Fecha</th>
          <th>Ciclo</th>
          <th class="text-right">Puntaje</th>
          <th>Estado</th>
          <th class="w-40"></th>
        </tr>
      </thead>

      <tbody>
        @forelse($sessions as $sess)
          @php
            [$label, $badge] = match ($sess->status) {
                \App\Models\EvaluationSession::STATUS_COMPLETED => ['Completada', 'co-badge co-badge--success'],
                \App\Models\EvaluationSession::STATUS_SIGNED => ['Pend. tu firma', 'co-badge co-badge--warning'],
                \App\Models\EvaluationSession::STATUS_DRAFT => ['Borrador', 'co-badge co-badge--neutral'],
                \App\Models\EvaluationSession::STATUS_PENDING => ['Pendiente', 'co-badge co-badge--info'],
                \App\Models\EvaluationSession::STATUS_IN_PROGRESS => ['En progreso', 'co-badge co-badge--info'],
                \App\Models\EvaluationSession::STATUS_CANCELLED => ['Cancelada', 'co-badge co-badge--danger'],
                default => [ucfirst($sess->status), 'co-badge co-badge--neutral'],
            };
          @endphp

          <tr class="co-tr">
            <td>{{ $sess->id }}</td>
            <td>{{ $sess->date?->format('d/m/Y') }}</td>
            <td>{{ $sess->cycle }}</td>
            <td class="text-right">{{ number_format(((float) ($sess->overall_avg ?? 0)) * 100, 2) }}%</td>
            <td>
              <span class="{{ $badge }}">{{ $label }}</span>
            </td>
            <td class="text-right">
              <div class="flex justify-end gap-1">
                <flux:button :href="route('coachee.sessions.show', $sess)" as="a" icon="eye" size="sm"
                  title="Ver detalle" variant="ghost" />

                {{-- Firmar: cuando el coach ya firmó => status SIGNED y el coachee aún no --}}
                @if (
                    $sess->status === \App\Models\EvaluationSession::STATUS_SIGNED &&
                        !$sess->signatures->contains(fn($sig) => $sig->user_id === auth()->id() && $sig->status === 'signed'))
                  <flux:button :href="route('coachee.sessions.sign.form', $sess)" as="a" icon="pencil"
                    size="sm" title="Firmar" variant="primary" />
                @endif

                {{-- Descargar PDF: solo cuando ambas firmas => COMPLETED --}}
                @if ($sess->status === \App\Models\EvaluationSession::STATUS_COMPLETED && $sess->pdf_path)
                  <flux:button :href="route('coachee.sessions.download', $sess)" as="a" icon="arrow-down-tray"
                    size="sm" title="Descargar PDF" variant="ghost" />
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td class="px-4 py-3 text-center text-sm text-slate-500 dark:text-slate-400" colspan="6">
              No se encontraron sesiones.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="co-pagination">
      {{ $sessions->links() }}
    </div>
  </div>
</div>
