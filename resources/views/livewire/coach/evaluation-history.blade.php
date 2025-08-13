<div class="co-page space-y-6">
    <h1 class="co-title">Historial de Evaluaciones</h1>

    {{-- Filtros + Export --}}
    <div class="co-panel">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Coachee --}}
            <div>
                <label class="co-label">Coachee</label>
                <flux:select class="co-input mt-1 w-full" wire:model.defer="filters.participant">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach ($participants as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- División --}}
            <div>
                <label class="co-label">División</label>
                <flux:select class="co-input mt-1 w-full" wire:model.defer="filters.division">
                    <flux:select.option value="">Todas</flux:select.option>
                    @foreach ($divisions as $d)
                        <flux:select.option value="{{ $d->id }}">{{ $d->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Ciclo --}}
            <div>
                <label class="co-label">Ciclo</label>
                <flux:select class="mt-1 block w-full" placeholder="Todos" wire:model.defer="filters.cycle">
                    @foreach ($cycles as $c)
                        <flux:select.option value="{{ $c }}">{{ $c }}</flux:select.option>
                    @endforeach
                </flux:select>

            </div>

            {{-- Estado --}}
            <div>
                <label class="co-label">Estado</label>
                <flux:select class="co-input mt-1 w-full" wire:model.defer="filters.status">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach ($statuses as $code => $label)
                        <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Fecha --}}
            <div class="lg:col-span-2">
                <label class="co-label">Fecha desde / hasta</label>
                <div class="mt-1 flex flex-wrap gap-2">
                    <flux:input class="co-input flex-1" type="date" wire:model.defer="filters.from" />
                    <flux:input class="co-input flex-1" type="date" wire:model.defer="filters.to" />
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-3">
                <flux:button class="w-full sm:w-auto" variant="primary" wire:click.prevent="$refresh">
                    Filtrar
                </flux:button>

                <flux:button class="w-full sm:w-auto" variant="outline" wire:click.prevent="resetFilters">
                    Limpiar
                </flux:button>

                <flux:button class="w-full sm:w-auto" color="orange" icon="arrow-down-tray" variant="primary"
                    wire:click.prevent="exportToExcel">
                    Exportar a Excel
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="co-panel overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        Coachee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        División</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        Ciclo</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        Puntaje</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600 dark:text-slate-300">
                        Estado</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                @forelse($sessions as $sess)
                    <tr wire:key="row-{{ $sess->id }}">
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $sess->id }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                            {{ optional($sess->date)?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $sess->participant->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                            {{ $sess->division->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $sess->cycle ?? '—' }}</td>
                        <td class="px-6 py-4 text-right text-sm text-slate-900 dark:text-slate-100">
                            {{ number_format(((float) ($sess->overall_avg ?? 0)) * 100, 2) }}%
                        </td>
                        <td class="px-6 py-4">
                            <flux:badge :color="$sess->status_pill_color" class="rounded-full" variant="soft">
                                {{ $sess->status_name }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if ($sess->isCompleted() || $sess->isSigned())
                                <flux:button :href="route('evaluation.summary', $sess)" as="a" icon="eye"
                                    size="sm" variant="ghost" color="emerald" />
                            @else
                                <flux:button :href="route('evaluation.fill', $sess)" as="a" icon="folder-plus"
                                    size="sm" variant="ghost" />
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-slate-400" colspan="8">
                            No se encontraron sesiones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/60">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
