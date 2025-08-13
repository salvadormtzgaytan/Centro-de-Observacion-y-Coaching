<?php

namespace App\Services\Pdf;

use App\Models\EvaluationSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvaluationPdfService
{
    /**
     * Genera un PDF de evaluación con gráficas incluidas
     *
     * @param EvaluationSession $session Modelo de la sesión de evaluación
     * @param array $charts Array de gráficas con estructura:
     *        [
     *            [
     *                'namechart' => 'Nombre de la gráfica',
     *                'imagechart' => 'data:image/png;base64,...', // Imagen en base64
     *                'description' => 'Descripción opcional'
     *            ],
     *            ...
     *        ]
     * @return string Ruta relativa del archivo PDF generado en el storage
     * @throws \RuntimeException Si falla la generación del PDF
     */
    public function generatePdf(EvaluationSession $session, array $charts = []): string
    {
        try {
            // Validar y normalizar las gráficas
            $normalizedCharts = $this->normalizeCharts($charts);

            // Crear directorio si no existe
            $this->ensureStorageDirectoryExists($session);
            // Generar y guardar el PDF
            return $this->generateAndSavePdf($session, $normalizedCharts);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error al generar el PDF: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Descarga el PDF de evaluación
     *
     * @param EvaluationSession $session Modelo de la sesión de evaluación
     * @param array $charts Array de gráficas (opcional, para regenerar si es necesario)
     * @return StreamedResponse Respuesta de descarga del archivo
     * @throws \RuntimeException Si falla la generación o descarga del PDF
     */
    public function downloadPdf(EvaluationSession $session, array $charts = []): StreamedResponse
    {
        try {
            // Regenerar PDF solo si no existe o se proporcionan nuevas gráficas
            if ($this->shouldRegeneratePdf($session, $charts)) {
                $pdfPath = $this->generatePdf($session, $charts);
                $session->update(['pdf_path' => $pdfPath]);
            }

            return $this->createDownloadResponse($session);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error al descargar el PDF: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Normaliza las gráficas para el PDF
     *
     * @param array $charts Gráficas a normalizar
     * @return array Gráficas normalizadas
     */
    protected function normalizeCharts(array $charts): array
    {
        return array_map(function ($chart) {
            return [
                'name' => $chart['namechart'] ?? 'Gráfica sin nombre',
                'image' => $this->normalizeChartImage($chart['imagechart'] ?? ''),
                'description' => $chart['description'] ?? ''
            ];
        }, $charts);
    }

    /**
     * Normaliza la imagen de la gráfica (asegura formato base64 válido)
     *
     * @param string $image Imagen a normalizar
     * @return string Imagen en formato data URL base64
     */
    protected function normalizeChartImage(string $image): string
    {
        // Si ya es un data URL base64, retornar tal cual
        if (Str::startsWith($image, 'data:image')) {
            return $image;
        }

        // Si es binario, convertirlo a base64
        return 'data:image/png;base64,' . base64_encode($image);
    }

    /**
     * Asegura que exista el directorio de almacenamiento
     *
     * @param EvaluationSession $session Modelo de la sesión
     */
    protected function ensureStorageDirectoryExists(EvaluationSession $session): void
    {
        $directory = "evaluations/{$session->id}";
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
    }

    /**
     * Genera y guarda el PDF en el almacenamiento
     *
     * @param EvaluationSession $session Modelo de la sesión
     * @param array $charts Gráficas normalizadas
     * @return string Ruta del archivo generado
     */
    protected function generateAndSavePdf(EvaluationSession $session, array $charts): string
    {
        $filename = "evaluations/{$session->id}/session-" . now()->format('YmdHis') . '.pdf';
        // Promedios globales (persistidos) de la sesión
        $overallAvg = $session->overall_avg; // 0..1 o null
        $overallAvgPct = $session->overall_avg_pct; // 0..100 o null
        $answeredAvg = $session->answered_avg; // 0..1 o null
        $answeredAvgPct = $session->answered_avg_pct; // 0..100 o null
        $maxScore = (float) $session->max_score;
        $totalScore = (float) $session->total_score;
        /** @var \Illuminate\Support\Collection $guides */
        $guides = $session->guideResponses->map(function ($gr) {
            return [
                'id' => $gr->id,
                'template_name' => $gr->guideTemplate->name ?? $gr->guideTemplate->title ?? '—',
                'total_score' => (float) ($gr->total_score ?? 0.0),
                'sections' => $gr->getSectionAverages()
                    ->map(fn($section) => [
                        'id' => $section->section_id,
                        'title' => $section->section_title,
                        'planned' => $section->planned,
                        'answered' => $section->answered,
                        'avg' => $section->avg, // ← ya redondeado y en %
                    ])
                    ->values() // ← si prefieres índices numéricos
                    ->all(),
            ];
        });

        $data = [
            'session' => $session,
            'charts' => $charts,
            'guides' => $guides,
            'overallAvg' => $overallAvg,
            'overallAvgPct' => $overallAvgPct,
            'answeredAvg' => $answeredAvg,
            'answeredAvgPct' => $answeredAvgPct,
            'maxScore' => $maxScore,
            'totalScore' => $totalScore
        ];
        Pdf::loadView('pdf.evaluation', $data)
            ->setPaper('letter', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('dpi', 96)
            ->setOption('defaultFont', 'DejaVu Sans')
            // MEMORIA Y RENDIMIENTO
            ->setOption('chroot', storage_path('app'))   // Directorio raíz para recursos
            ->setOption('logOutputFile', storage_path('logs/dompdf.log')) // Log de errores
            ->setOption('tempDir', sys_get_temp_dir())   // Directorio temporal
            ->save(Storage::path($filename));

        return $filename;
    }

    /**
     * Determina si se debe regenerar el PDF
     *
     * @param EvaluationSession $session Modelo de la sesión
     * @param array $charts Gráficas nuevas (opcional)
     * @return bool True si se debe regenerar el PDF
     */
    protected function shouldRegeneratePdf(EvaluationSession $session, array $charts): bool
    {
        return !$session->pdf_path || !Storage::exists($session->pdf_path) || !empty($charts);
    }

    /**
     * Crea la respuesta de descarga del PDF
     *
     * @param EvaluationSession $session Modelo de la sesión
     * @return StreamedResponse Respuesta de descarga
     */
    protected function createDownloadResponse(EvaluationSession $session): StreamedResponse
    {
        return Storage::download(
            $session->pdf_path,
            "evaluacion-{$session->id}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
