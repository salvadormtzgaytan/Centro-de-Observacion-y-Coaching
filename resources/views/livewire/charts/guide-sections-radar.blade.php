<div>
    {{-- Carga del bundle del radar --}}
    @once
        @vite('resources/js/components/radar.js')
        <style>
            :root {
                --radar-stroke: #0f172a;
                /* slate-900 */
                --radar-fill: rgba(99, 102, 241, 0.2);
                /* indigo-500/20 */
                --radar-grid: rgba(15, 23, 42, 0.15);
                /* slate-900/15 */
                --radar-point: #6366f1;
                /* indigo-500 */
            }

            .dark:root {
                --radar-stroke: #e5e7eb;
                /* slate-200 */
                --radar-fill: rgba(99, 102, 241, .25);
                --radar-grid: rgba(226, 232, 240, .2);
                /* slate-200/20 */
                --radar-point: #a5b4fc;
                /* indigo-300 */
            }
        </style>
    @endonce

    {{-- Componente de presentación --}}
    @if (!empty($radar->labels) && !empty($radar->datasets))
        <x-charts.radar :data="$radar" :height="$height" />
    @else
        <div
            class="flex h-full items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                No hay datos calificables disponibles para mostrar el gráfico
            </p>
        </div>
    @endif

    {{-- Badges: Respondidas / Planificadas + Promedio por sección --}}
    @if (collect($sections)->isNotEmpty())
        <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sections as $s)
                @php
                    // Los valores ya vienen en 0-100 desde getSectionAverages()
                    $avgPct = $s->avg; // Ya está en porcentaje (0-100)
                    $avgTitle = $avgModeZF
                        ? __('Promedio (sobre :planned planificadas; no respondidas = 0)', ['planned' => $s->planned])
                        : __('Promedio (solo :answered respondidas)', ['answered' => $s->answered]);
                @endphp

                <li
                    class="flex items-center justify-between rounded border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <span class="truncate text-sm text-slate-700 dark:text-slate-200" title="{{ $s->section_title }}">
                        {{ Str::limit($s->section_title, 25) }}
                    </span>

                    <span class="flex items-center gap-2">
                        <span class="co-badge co-badge--info whitespace-nowrap" title="Respondidas / Planificadas">
                            {{ $s->answered }} / {{ $s->planned }}
                        </span>

                        <span class="co-badge co-badge--success whitespace-nowrap" title="{{ $avgTitle }}">
                            @if ($avgPct !== null)
                                {{ number_format($avgPct, 1) }}%
                                <span class="ml-1 opacity-60">
                                    @if ($avgPct >= 80)
                                        ⭐
                                    @elseif($avgPct >= 60)
                                        ✓
                                    @endif
                                </span>
                            @else
                                —
                            @endif
                        </span>
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                No se encontraron secciones con items calificables
            </p>
        </div>
    @endif
</div>
