<?php

namespace App\Http\Controllers;

use App\Models\EvaluationSession;
use App\Services\Pdf\EvaluationPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvaluationPdfController extends Controller
{
    protected EvaluationPdfService $pdfService;

    public function __construct(EvaluationPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function __invoke(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'session_id' => 'required|exists:evaluation_sessions,id',
                'charts' => 'required|array',
                'charts.*.namechart' => 'required|string',
                'charts.*.imagechart' => 'required|string',
            ]);

            // Buscar la sesión con relaciones precargadas
            $session = EvaluationSession::with([
                'participant',
                'evaluator',
                'division',
                'signatures',
                'guideResponses',
                'guideResponses.itemResponses.item',
                // Agregar otras relaciones según sea necesario
            ])->findOrFail($validated['session_id']);

            // Verificar si ya existe un PDF generado
            if ($session->pdf_path && Storage::exists($session->pdf_path) && ! config('app.debug')) {
                return response()->json([
                    'success' => true,
                    'download_url' => Storage::url($session->pdf_path),
                    'pdf_path' => $session->pdf_path,
                    'filename' => "evaluacion-{$session->id}.pdf",
                    'message' => 'PDF previamente generado',
                ]);
            }

            // Validación adicional de relaciones
            if (! $session->participant || ! $session->evaluator) {
                throw new \Exception('La sesión no tiene todos los participantes requeridos');
            }

            // Generar PDF usando el servicio
            $pdfPath = $this->pdfService->generatePdf($session, $validated['charts']);

            // Actualizar la sesión con la nueva ruta del PDF
            $session->update(['pdf_path' => $pdfPath]);

            return response()->json([
                'success' => true,
                'download_url' => Storage::url($pdfPath),
                'pdf_path' => $pdfPath,
                'filename' => "evaluacion-{$session->id}.pdf",
                'message' => 'PDF generado exitosamente',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            report($e); // Registrar el error en los logs

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: '.$e->getMessage(),
            ], 500);
        }
    }
}
