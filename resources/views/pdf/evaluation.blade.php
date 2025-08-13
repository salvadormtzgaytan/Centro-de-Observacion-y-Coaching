<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Evaluación #{{ $session->id }} - {{ config('app.name') }}</title>
    @vite('resources/css/print.css')
</head>

<body>
    <!-- Header con logo y branding -->
    <div class="document-header">
        <table class="header-table" width="100%">
            <tr>
                <td class="logo-section" width="25%">
                    <img src="{{ public_path('images/logo.png') }}" class="company-logo" alt="{{ config('app.name') }}">
                </td>
                <td class="header-info" width="50%">
                    <div class="company-name">{{ config('app.name') }}</div>
                    <div class="document-type">Reporte de Evaluación de Coaching</div>
                    <div class="document-subtitle">Análisis de Desempeño y Desarrollo</div>
                </td>
                <td class="evaluation-badge" width="25%">
                    <div class="evaluation-number">#{{ $session->id }}</div>
                    <div class="evaluation-date">{{ $session->date?->format('d/m/Y') ?? 'N/A' }}</div>
                    <div class="badge badge--info">
                        {{ $session->status === 'completed' ? 'Completada' : ($session->status === 'pending' ? 'Pendiente' : 'En Proceso') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider-line"></div>

    <div class="page-container">
        <div class="content-wrapper">

            <!-- Resumen Ejecutivo -->
            <div class="executive-summary card">
                <div class="card-header">
                    <div class="h2">Resumen Ejecutivo</div>
                </div>
                <div class="card-content">
                    <table class="summary-grid" width="100%">
                        <tr>
                            <td width="50%" class="summary-left">
                                <div class="participant-info">
                                    <div class="participant-label">EVALUADO</div>
                                    <div class="participant-name">{{ $session->participant->name ?? '—' }}</div>
                                    <div class="participant-role">{{ $session->participant->email ?? '' }}</div>
                                </div>

                                <div class="coach-info mt-10">
                                    <div class="coach-label">EVALUADOR</div>
                                    <div class="coach-name">{{ $session->evaluator->name ?? '—' }}</div>
                                    <div class="coach-credentials">
                                        {{ $session->evaluator->email ?? '' }}</div>
                                </div>
                            </td>
                            <td width="50%" class="summary-right">
                                <div class="score-highlight">
                                    <div class="score-label">PUNTAJE GLOBAL</div>
                                    <div
                                        class="score-value {{ $overallAvg >= 0.8 ? 'score--excellent' : ($overallAvg >= 0.6 ? 'score--good' : 'score--needs-improvement') }}">
                                        {{ $overallAvg !== null ? number_format($overallAvg * 100, 1) . '%' : '—' }}
                                    </div>
                                    <div class="score-description">
                                        @if ($overallAvg >= 0.8)
                                            Excelente desempeño
                                        @elseif($overallAvg >= 0.6)
                                            Buen desempeño
                                        @else
                                            Requiere mejora
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $progressPercent = $overallAvgPct ?? 0;
                                    $progress = $overallAvg ?? null;
                                @endphp

                                <div class="progress-highlight mt-10">
                                    <div class="progress-label">PROGRESO DE EVALUACIÓN</div>
                                    <table class="progress-enhanced" width="100%">
                                        <tr>
                                            <td class="progress__bar">
                                                <div class="progress__fill" style="width: {{ $progressPercent }}%;">
                                                </div>
                                            </td>
                                            <td class="progress__pct">{{ $progressPercent }}%</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Métricas Clave -->
            @if ($guides->isNotEmpty())
                <div class="section">
                    <div class="section-header">
                        <div class="section__title">Análisis por Plantilla de Evaluación</div>
                        <div class="section-description">Desglose detallado del rendimiento por área de competencia
                        </div>
                    </div>

                    <div class="metrics-grid">
                        @foreach ($guides as $guide)
                            @php
                                $percentage = number_format($guide['total_score'] * 100, 2);
                            @endphp

                            <div class="metric-card">
                                <div class="metric-header">
                                    <div class="metric-title">{{ $guide['template_name'] }}</div>
                                    <div class="metric-score">{{ $percentage }}%</div>
                                </div>
                                <div class="metric-progress">
                                    <table class="progress" width="100%">
                                        <tr>
                                            <td class="progress__bar">
                                                <div class="progress__fill" style="width: {{ $percentage }}%;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                @if (!empty($guide['sections']))
                                    <table class="table table--compact" width="100%" style="margin-top: 8px;">
                                        <thead>
                                            <tr>
                                                <th>Sección</th>
                                                <th class="text-right">Ítems</th>
                                                <th class="text-right">Respondidos</th>
                                                <th class="text-right">Promedio</th>
                                                <th>Progreso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($guide['sections'] as $section)
                                                <tr>
                                                    <td>{{ $section['title'] }}</td>
                                                    <td class="text-right">{{ $section['planned'] }}</td>
                                                    <td class="text-right">{{ $section['answered'] }}</td>
                                                    <td class="text-right">{{ number_format($section['avg'], 2) }}%
                                                    </td>
                                                    <td>
                                                        <table class="progress" width="100%">
                                                            <tr>
                                                                <td class="progress__bar">
                                                                    <div class="progress__fill"
                                                                        style="width: {{ $section['avg'] }}%;"></div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="page-break"></div>
            <!-- Análisis Visual -->
            @if (count($charts) > 0)
                <div class="section">
                    <div class="section-header">
                        <div class="section__title">Análisis Visual de Resultados</div>
                        <div class="section-description">Representación gráfica del desempeño y tendencias identificadas
                        </div>
                    </div>

                    @foreach ($charts as $chart)
                        <div class="chart-container-enhanced card">
                            <div class="chart-header">
                                <div class="chart-title">{{ $chart['name'] }}</div>
                                @if (!empty($chart['description']))
                                    <div class="chart-description">{{ $chart['description'] }}</div>
                                @endif
                            </div>
                            <div class="chart-content">
                                <img src="{{ $chart['image'] }}" class="chart-image" alt="{{ $chart['name'] }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Recomendaciones -->
            <div class="section">
                <div class="recommendations-section card">
                    <div class="card-header">
                        <div class="section__title">Recomendaciones para el Desarrollo</div>
                    </div>
                    <div class="card-content">
                        <div class="recommendations-grid">
                            <div class="recommendation-item">
                                <div class="recommendation-icon">🎯</div>
                                <div class="recommendation-content">
                                    <div class="recommendation-title">Áreas de Fortaleza</div>
                                    <div class="recommendation-text">
                                        @if ($overallAvg >= 0.8)
                                            Mantener el excelente nivel de desempeño y considerar oportunidades de
                                            mentoría a otros colaboradores.
                                        @elseif($overallAvg >= 0.6)
                                            Consolidar las competencias adquiridas y enfocar esfuerzos en áreas
                                            específicas de mejora.
                                        @else
                                            Desarrollar un plan de mejora enfocado en las competencias fundamentales
                                            identificadas.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="recommendation-item">
                                <div class="recommendation-icon">📈</div>
                                <div class="recommendation-content">
                                    <div class="recommendation-title">Próximos Pasos</div>
                                    <div class="recommendation-text">
                                        Programar sesiones de seguimiento para monitorear el progreso y ajustar el plan
                                        de desarrollo según sea necesario.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-break"></div>

            <div class="section">
                <div class="section-header">
                    <div class="section__title">Detalle de Respuestas por Plantilla</div>
                    <div class="section-description">Relación de preguntas evaluadas y sus respectivas respuestas</div>
                </div>

                @foreach ($session->guideResponses as $gr)
                    <div class="card mb-15">
                        <div class="card-header">
                            <div class="h3">{{ $gr->guideTemplate->name ?? 'Plantilla' }}</div>
                        </div>
                        <div class="card-content">
                            <table width="100%" class="table table--compact" style="border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th>Pregunta</th>
                                        <th style="width: 20%">Valoración</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gr->itemResponses as $i => $response)
                                        @php
                                            $item = $response->item;
                                            $score = is_numeric($response->score_obtained)
                                                ? number_format($response->score_obtained, 2)
                                                : '—';
                                            $label = \App\Models\Scale::getLabelForValue($response->score_obtained);
                                        @endphp
                                        @if (!$item->isScorable())
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td colspan="2">
                                                    <b>{!! $item->question ?? '—' !!}</b><br>
                                                    @php
                                                        $answerText = '—';
                                                        if (is_array($response->answer) && isset($response->answer[0]['value'])) {
                                                            $answerText = strip_tags($response->answer[0]['value']);
                                                        } elseif (is_string($response->answer)) {
                                                            $answerText = strip_tags($response->answer);
                                                        }
                                                    @endphp
                                                    {!! $answerText !!}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td>{!! $item->question ?? '—' !!}</td>
                                                <td class="text-center">{{ $label }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Firmas y Validaciones -->
            <div class="section">
                <div class="signatures-section card">
                    <div class="card-header">
                        <div class="section__title">Validación y Firmas</div>
                    </div>
                    <div class="card-content">
                        <table class="signatures-enhanced" width="100%">
                            <tr>
                                @foreach (['coach' => 'COACH EVALUADOR', 'coachee' => 'EVALUADO'] as $role => $label)
                                    @php
                                        $sig = $session->getSignatureData($role);
                                    @endphp
                                    <td class="signature-cell">
                                        <div class="signature-header">
                                            <div class="signature-role">{{ $label }}</div>
                                            <div class="signature-name">{{ $sig['name'] }}</div>
                                        </div>

                                        <div class="signature-area">
                                            @if ($sig['image_url'])
                                                <img src="{{ $sig['image_url'] }}" class="signature-image">
                                                <div class="signature-line"></div>
                                            @else
                                                <div class="signature-placeholder">
                                                    <div class="signature-pending">Pendiente de firma</div>
                                                </div>
                                                <div class="signature-line signature-line--dashed"></div>
                                            @endif
                                        </div>

                                        <div class="signature-details">
                                            <div class="signature-status-text">
                                                <strong>Estado:</strong> {{ $sig['status'] }}
                                            </div>
                                            <div class="signature-date">
                                                <strong>Fecha:</strong> {{ $sig['signed_at'] }}
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer Profesional -->
    <div class="document-footer">
        <table class="footer-table" width="100%">
            <tr>
                <td class="footer-left" width="60%">
                    <div class="footer-company">{{ config('app.name') }}</div>
                    <div class="footer-tagline">Centro de Desarrollo y Excelencia Profesional</div>
                </td>
                <td class="footer-right" width="40%">
                    <div class="generation-info">
                        <div class="generated-label">Documento generado el</div>
                        <div class="generated-date">{{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
