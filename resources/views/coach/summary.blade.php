<x-layouts.app :title="__('Mis Evaluaciones')">
    <div class="co-page">
        {{-- Header + acciones --}}
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="co-title">Evaluación #{{ $session->id }}</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    {{ $session->cycle_full_name ?? ($session->cycle ?? '—') }} ·
                    {{ $session->date?->format('d/m/Y') ?? '—' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- Volver al listado --}}
                <flux:button as="a" href="{{ route('evaluation.history') }}" icon="arrow-left" variant="ghost">
                    Volver
                </flux:button>
                {{-- Descargar PDF: sólo con ambas firmas => COMPLETED --}}
                @if ($session->status === \App\Models\EvaluationSession::STATUS_COMPLETED)
                    <button class="bg-primary-600 cursor-pointer" id="generateChartsPdf"" icon="arrow-down-tray"
                        variant="filled" data-sessionid="{{ $session->id }}"
                        data-pdf-route="{{ route('service.pdf.summary') }}">
                        Descargar PDF
                    </button>
                @endif
            </div>
        </div>

        {{-- Alerts flash --}}
        @if (session('success'))
            <div
                class="mt-3 rounded-md bg-emerald-50 p-3 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="mt-3 rounded-md bg-amber-50 p-3 text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                {{ session('warning') }}
            </div>
        @endif

        {{-- Resumen --}}
        <div class="co-section mt-4">
            @php
                [$statusLabel, $badgeClass] = match ($session->status) {
                    \App\Models\EvaluationSession::STATUS_COMPLETED => ['Completada', 'co-badge co-badge--success'],
                    \App\Models\EvaluationSession::STATUS_SIGNED => ['Por firmar', 'co-badge co-badge--warning'],
                    \App\Models\EvaluationSession::STATUS_DRAFT => ['Borrador', 'co-badge co-badge--neutral'],
                    \App\Models\EvaluationSession::STATUS_PENDING => ['Pendiente', 'co-badge co-badge--info'],
                    \App\Models\EvaluationSession::STATUS_IN_PROGRESS => ['En progreso', 'co-badge co-badge--info'],
                    \App\Models\EvaluationSession::STATUS_CANCELLED => ['Cancelada', 'co-badge co-badge--danger'],
                    default => [ucfirst((string) $session->status), 'co-badge co-badge--neutral'],
                };
            @endphp

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Estado</div>
                    <div class="mt-1"><span class="{{ $badgeClass }}">{{ $statusLabel }}</span></div>
                </div>

                <div>
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Evaluado</div>
                    <div class="mt-1 text-slate-800 dark:text-slate-100">{{ $session->participant->name ?? '—' }}</div>
                </div>

                {{-- Puntaje total (0–100%) usando overallAvg --}}
                <div>
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Puntaje total</div>
                    <div class="mt-1 text-slate-800 dark:text-slate-100">
                        {{ $overallAvg !== null ? number_format($overallAvg * 100, 2) . '%' : '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Progreso</div>
                    <div class="mt-1 flex items-center gap-2">
                        <div class="h-2 w-full overflow-hidden rounded bg-slate-200 dark:bg-slate-700">
                            <div class="h-2 rounded bg-emerald-500"
                                style="width: {{ $session->progress_percent ?? 0 }}%"></div>
                        </div>
                        <div class="min-w-[3rem] text-right text-sm text-slate-800 dark:text-slate-100">
                            {{ $session->progress_percent ?? 0 }}%
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $session->answered_items_count ?? 0 }} / {{ $session->planned_items_count ?? 0 }} ítems
                    </div>
                </div>
            </div>
        </div>

        {{-- Plantillas evaluadas (si existen) --}}
        @if ($session->relationLoaded('guideResponses') || $session->guideResponses()->exists())
            <div class="co-section mt-4">
                <h2 class="mb-3 text-base font-semibold text-slate-800 dark:text-slate-100">Plantillas evaluadas</h2>

                <div class="overflow-x-auto">
                    <table class="co-table">
                        <thead>
                            <tr>
                                <th>Plantilla</th>
                                <th class="text-right">Puntaje</th>
                                <th class="text-right">Respuestas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($session->guideResponses as $gr)
                                <tr>
                                    <td>{{ $gr->guideTemplate->name ?? ($gr->guideTemplate->title ?? '—') }}</td>
                                    <td class="text-right">
                                        {{ isset($guideScores[$gr->id]) ? number_format($guideScores[$gr->id] * 100, 2) . '%' : '—' }}
                                    </td>
                                    <td class="text-right">{{ $gr->itemResponses()->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Radar por secciones (uno por cada guía respondida en la sesión) --}}
        @if ($session->guideResponses->isNotEmpty())
            <div class="co-section mt-6">
                <h2 class="mb-3 text-base font-semibold text-slate-800 dark:text-slate-100">Radar por secciones</h2>

                @foreach ($session->guideResponses as $gr)
                    <div class="mb-6">
                        <div class="mb-2 text-sm text-slate-600 dark:text-slate-300">
                            {{ $gr->guideTemplate->name ?? 'Guía' }}
                        </div>

                        <livewire:charts.guide-sections-radar :guide-response-id="$gr->id" :unanswered-as-zero="true"
                            dataset-label="Promedio sesión" mode="response" />
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Firmas --}}
        @php
            $coachSig = $session->signatures->firstWhere('signer_role', 'coach');
            $coacheeSig = $session->signatures->firstWhere('signer_role', 'coachee');
            $coachSigUrl =
                $coachSig && $coachSig->digital_signature
                    ? asset('storage/' . ltrim($coachSig->digital_signature, '/'))
                    : null;
            $coacheeSigUrl =
                $coacheeSig && $coacheeSig->digital_signature
                    ? asset('storage/' . ltrim($coacheeSig->digital_signature, '/'))
                    : null;
        @endphp

        <div class="co-section mt-4">
            <h2 class="mb-3 text-base font-semibold text-slate-800 dark:text-slate-100">Firmas</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                {{-- Coach --}}
                <div class="rounded border border-slate-200 p-4 dark:border-slate-700">
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Coach</div>
                    <div class="mt-1 font-medium text-slate-800 dark:text-slate-100">
                        {{ $session->evaluator->name ?? '—' }}</div>

                    <div class="mt-3 flex h-24 items-center justify-center rounded bg-slate-50 dark:bg-slate-800/50">
                        @if ($coachSigUrl)
                            <img alt="Firma del coach" class="max-h-20 object-contain" src="{{ $coachSigUrl }}">
                        @else
                            <span class="text-sm text-slate-400">Sin firma</span>
                        @endif
                    </div>

                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        Estado: {{ $coachSig->status ?? '—' }} ·
                        {{ $coachSig?->signed_at?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>

                {{-- Coachee --}}
                <div class="rounded border border-slate-200 p-4 dark:border-slate-700">
                    <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Coachee</div>
                    <div class="mt-1 font-medium text-slate-800 dark:text-slate-100">
                        {{ $session->participant->name ?? '—' }}
                    </div>

                    <div class="mt-3 flex h-24 items-center justify-center rounded bg-slate-50 dark:bg-slate-800/50">
                        @if ($coacheeSigUrl)
                            <img alt="Tu firma" class="max-h-20 object-contain" src="{{ $coacheeSigUrl }}">
                        @else
                            <span class="text-sm text-slate-400">Sin firma</span>
                        @endif
                    </div>

                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        Estado:
                        {{ $coacheeSig->status ?? ($session->status === \App\Models\EvaluationSession::STATUS_SIGNED ? 'pendiente' : '—') }}
                        ·
                        {{ $coacheeSig?->signed_at?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/components/evaluation-pdf-export.js')
</x-layouts.app>
