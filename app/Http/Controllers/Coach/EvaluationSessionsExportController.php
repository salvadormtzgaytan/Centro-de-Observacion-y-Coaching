<?php

namespace App\Http\Controllers\Coach;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EvaluationSessionsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvaluationSessionsExportController extends Controller
{
    /**
     * Descarga el historial de evaluaciones filtrado a Excel.
     *
     * @param  Request  $request
     * @return BinaryFileResponse
     */
    public function __invoke(Request $request): BinaryFileResponse
    {
        // Recolectar filtros de query string
        $filters = $request->only([
            'participant', 'division', 'cycle', 'status', 'from', 'to'
        ]);

        // Nombre de archivo con timestamp
        $fileName = 'historial_evaluaciones_' . now()->format('Ymd_His') . '.xlsx';

        // Iniciar descarga
        return Excel::download(
            new EvaluationSessionsExport($request->user()->id, $filters),
            $fileName
        );
    }
}
