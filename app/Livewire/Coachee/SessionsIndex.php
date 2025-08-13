<?php

declare(strict_types=1);

namespace App\Livewire\Coachee;

use App\Exports\CoacheeSessionsExport;
use App\Models\EvaluationSession;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SessionsIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    /**
     * Filtros visibles en la UI.
     *
     * @var array{status:string,cycle:string,from:?string,to:?string}
     */
    public array $filters = [
        'status' => '',
        'cycle'  => '',
        'from'   => null,
        'to'     => null,
    ];

    public int $perPage = 15;

    public function mount(): void
    {
        // Autorización vía Policy
        Gate::authorize('viewAny', EvaluationSession::class);
    }

    public function updating($name, $value): void
    {
        // Reset de paginación cuando cambian filtros
        if (str_starts_with($name, 'filters.')) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'status' => '',
            'cycle'  => '',
            'from'   => null,
            'to'     => null,
        ];
        $this->resetPage();
    }

    public function exportToExcel()
    {
        [$query] = $this->baseQuery();

        $labels = $this->statuses;

        $rows = $query
            ->get(['id', 'date', 'cycle', 'overall_avg', 'status'])
            ->map(function ($s) use ($labels) {
                // overall_avg persiste en 0..1 (o null); exportamos 0..100 como número
                $pct = $s->overall_avg !== null ? round(((float) $s->overall_avg) * 100, 2) : 0.0;

                return [
                    'ID'      => $s->id,
                    'Fecha'   => optional($s->date)->format('d/m/Y'),
                    'Ciclo'   => $s->cycle,
                    'Puntaje' => $pct, // número 0–100 en Excel
                    'Estado'  => $labels[$s->status] ?? ucfirst((string) $s->status),
                ];
            });

        return Excel::download(new CoacheeSessionsExport($rows), 'mis-evaluaciones.xlsx');
    }

    /**
     * Query base + filtros aplicados.
     *
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:int}
     */
    protected function baseQuery(): array
    {
        $userId = Auth::id();

        $query = EvaluationSession::query()
            ->with(['signatures'])
            ->where('participant_id', $userId)
            ->where('status', '!=', EvaluationSession::STATUS_DRAFT)
            // No subselects: usamos métricas ya persistidas en evaluation_sessions
            ->when($this->filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($this->filters['cycle'],  fn ($q, $v) => $q->where('cycle', $v))
            ->when($this->filters['from'],   fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($this->filters['to'],     fn ($q, $v) => $q->whereDate('date', '<=', $v));

        return [$query, $userId];
    }

    /** Distinct de ciclos del usuario actual */
    public function getCyclesProperty()
    {
        return EvaluationSession::query()
            ->where('participant_id', Auth::id())
            ->whereNotNull('cycle')
            ->distinct()
            ->orderBy('cycle')
            ->pluck('cycle');
    }

    /**
     * Catálogo de estados para el select y para exportación.
     * NOTA: 'SIGNED' se muestra como "Por firmar" (coach ya firmó; falta coachee).
     */
    public function getStatusesProperty(): array
    {
        return [
            EvaluationSession::STATUS_IN_PROGRESS => 'En progreso',
            EvaluationSession::STATUS_PENDING     => 'Pendiente',
            EvaluationSession::STATUS_SIGNED      => 'Por firmar',   // coach ya firmó; falta coachee
            EvaluationSession::STATUS_COMPLETED   => 'Completada',   // ambas firmas
            EvaluationSession::STATUS_CANCELLED   => 'Cancelada',
        ];
    }

    public function render(): View
    {
        [$query] = $this->baseQuery();

        $sessions = $query
            ->orderByDesc('date')
            ->paginate($this->perPage);

        return view('livewire.coachee.sessions-index', [
            'sessions' => $sessions,
            'cycles'   => $this->cycles,
            'statuses' => $this->statuses,
        ]);
    }
}
