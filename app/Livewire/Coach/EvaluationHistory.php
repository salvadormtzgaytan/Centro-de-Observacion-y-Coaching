<?php

namespace App\Livewire\Coach;

use App\Models\Cycle;
use Livewire\Component;
use App\Models\Division;
use Livewire\WithPagination;
use App\Models\EvaluationSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EvaluationSessionsExport;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\NotifyUserEvaluationHistoryExportReady;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\TemplateItem as TI;

class EvaluationHistory extends Component
{
    use WithPagination, AuthorizesRequests;

    protected string $paginationTheme = 'tailwind';

    /** @var array{participant:string,division:string,cycle:string,status:string,from:string,to:string} */
    public array $filters = [
        'participant' => '',
        'division'    => '',
        'cycle'       => '',
        'status'      => '',
        'from'        => '',
        'to'          => '',
    ];

    protected array $queryString = [
        'filters.participant' => ['except' => ''],
        'filters.division'    => ['except' => ''],
        'filters.cycle'       => ['except' => ''],
        'filters.status'      => ['except' => ''],
        'filters.from'        => ['except' => ''],
        'filters.to'          => ['except' => ''],
        'page'                => ['except' => 1],
    ];

    public int $perPage = 10;

    public function mount(): void
    {
        Gate::authorize('viewAny', EvaluationSession::class);
    }

    public function updatingFilters(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'participant' => '',
            'division'    => '',
            'cycle'       => '',
            'status'      => '',
            'from'        => '',
            'to'          => '',
        ];
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $query = $this->buildQuery();
        $count = $query->count();

        if ($count === 0) {
            $this->dispatch('toast', type: 'error', message: 'No hay datos para exportar con los filtros actuales.', timer: 4000);
            return null;
        }

        $evaluatorId = Auth::id();
        $fileName    = 'historial_evaluaciones_' . now()->format('Ymd_His') . '.xlsx';
        $exportPath  = "exports/history/{$evaluatorId}/{$fileName}";

        try {
            $export = new EvaluationSessionsExport($evaluatorId, $this->filters);

            if ($count < 100) {
                return Excel::download($export, $fileName);
            }

            $export
                ->queue($exportPath, 'public')
                ->chain([
                    new NotifyUserEvaluationHistoryExportReady(Auth::user(), $exportPath),
                ]);

            $this->dispatch('toast', type: 'success', message: "Exportación encolada. Te avisaremos cuando esté lista. Registros: {$count}", timer: 5000);
            return null;
        } catch (\Throwable $e) {
            Log::error('[EvaluationHistory.exportToExcel] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', type: 'error', message: 'Error al exportar: ' . $e->getMessage(), timer: 5000);
            return null;
        }
    }

    /**
     * Base + filtros (excluye borradores).
     */
    private function buildQuery(): Builder
    {

        $q = EvaluationSession::query()
            ->where('evaluator_id', Auth::id())
            ->where('status', '!=', EvaluationSession::STATUS_DRAFT)
            ->with(['participant', 'division'])
            ->select('evaluation_sessions.*')
            ->selectSub(function ($sub) {
                $scoring = TI::allowedScorable();
                $sub->from('guide_responses as gr')
                    ->join('guide_item_responses as gir', 'gir.guide_response_id', '=', 'gr.id')
                    ->join('template_items as ti', 'ti.id', '=', 'gir.template_item_id')
                    ->whereColumn('gr.session_id', 'evaluation_sessions.id')
                    ->whereIn('ti.type', $scoring)
                    // promedio global 0..1 ponderado por # de ítems (solo respondidos)
                    ->selectRaw('AVG(gir.score_obtained)');
            }, 'overall_avg');

        if ($this->filters['participant'] !== '') $q->where('participant_id', $this->filters['participant']);
        if ($this->filters['division']    !== '') $q->where('division_id',    $this->filters['division']);
        if ($this->filters['cycle']       !== '') $q->where('cycle',          $this->filters['cycle']);
        if ($this->filters['status']      !== '') $q->where('status',         $this->filters['status']);
        if ($this->filters['from']        !== '') $q->whereDate('date', '>=', $this->filters['from']);
        if ($this->filters['to']          !== '') $q->whereDate('date', '<=', $this->filters['to']);

        return $q->orderByDesc('date');
    }


    /** Catálogo: participantes (descendientes). */
    public function getParticipantsProperty()
    {
        $user = Auth::user();
        return $user
            ? $user->descendants()->select(['id', 'name'])->orderBy('name')->get()
            : collect();
    }

    /** Catálogo: divisiones. */
    public function getDivisionsProperty()
    {
        return Division::select(['id', 'name'])->orderBy('name')->get();
    }

    /**
     * Catálogo: ciclos (strings) para el filtro.
     * Preferimos `cycles.key` (e.g. "FY2025-Q1"), y unimos con los ya usados en sesiones.
     */
    public function getCyclesProperty()
    {
        $defaultFy = (int) config('coaching.default_fiscal_year', now()->year);

        $fromCatalog = Cycle::query()
            ->select(['key', 'fiscal_year', 'quarter'])
            // primero el FY configurado, luego resto
            ->orderByRaw('fiscal_year = ? desc', [$defaultFy])
            ->orderBy('fiscal_year')
            ->orderBy('quarter')
            ->pluck('key');

        $fromSessions = EvaluationSession::query()
            ->where('evaluator_id', Auth::id())
            ->whereNotNull('cycle')
            ->distinct()
            ->orderBy('cycle')
            ->pluck('cycle');

        return $fromCatalog->merge($fromSessions)->unique()->values();
    }

    /** Catálogo: estados (sin 'draft'). */
    public function getStatusOptionsProperty(): array
    {
        $out = [];
        foreach (EvaluationSession::allowedStatuses() as $code) {
            if ($code === EvaluationSession::STATUS_DRAFT) continue;
            $out[$code] = EvaluationSession::labelForStatus($code);
        }
        return $out;
    }

    public function render()
    {
        $sessions = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.coach.evaluation-history', [
            'sessions'     => $sessions,
            'participants' => $this->participants,
            'divisions'    => $this->divisions,
            'cycles'       => $this->cycles,      // <- colección de strings "FY2025-Q1"
            'statuses'     => $this->statusOptions,
        ]);
    }
}
