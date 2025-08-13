<?php

namespace App\Exports;

use App\Models\EvaluationSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EvaluationSessionsExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    protected int $evaluatorId;
    protected array $filters;

    public function __construct(int $evaluatorId, array $filters = [])
    {
        $this->evaluatorId = $evaluatorId;
        $this->filters     = $filters;
    }


    public function query(): Builder
    {
        $q = EvaluationSession::query()
            ->where('evaluator_id', $this->evaluatorId)
            ->where('status', '!=', EvaluationSession::STATUS_DRAFT)
            ->with(['participant', 'division'])
            ->select('evaluation_sessions.*');

        // Filtros...
        if (!empty($this->filters['participant'])) $q->where('participant_id', $this->filters['participant']);
        if (!empty($this->filters['division']))    $q->where('division_id',    $this->filters['division']);
        if (!empty($this->filters['cycle']))       $q->where('cycle',          $this->filters['cycle']);
        if (!empty($this->filters['status']))      $q->where('status',         $this->filters['status']);
        if (!empty($this->filters['from']))        $q->whereDate('date', '>=', $this->filters['from']);
        if (!empty($this->filters['to']))          $q->whereDate('date', '<=', $this->filters['to']);

        return $q->orderByDesc('date');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Participante',
            'Email',
            'División',
            'Ciclo',
            'Fecha',
            'Estado',
            'Promedio (%)', // <-- antes decía "Puntuación"
            'Comentarios',
            'Creado',
            'Actualizado',
        ];
    }

    /**
     * @param \App\Models\EvaluationSession $session
     */
    public function map($session): array
    {
        // overall_avg 0..1 -> 0..100
        $pct = $session->overall_avg !== null ? round((float)$session->overall_avg * 100, 2) : 0.0;

        return [
            $session->id,
            $session->participant->name ?? '-',
            $session->participant->email ?? '-',
            $session->division->name ?? '-',
            $session->cycle ?? '-',
            optional($session->date)->format('d/m/Y'),
            EvaluationSession::labelForStatus($session->status),
            $pct, // Promedio (%)
            $this->cleanText($session->comments),
            optional($session->created_at)->format('d/m/Y H:i'),
            optional($session->updated_at)->format('d/m/Y H:i'),
        ];
    }


    private function cleanText(?string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
    }
}
