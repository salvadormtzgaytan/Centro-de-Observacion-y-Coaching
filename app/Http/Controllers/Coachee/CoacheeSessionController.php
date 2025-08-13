<?php

declare(strict_types=1);

namespace App\Http\Controllers\Coachee;

use App\Http\Controllers\Controller;
use App\Models\EvaluationSession;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoacheeSessionController extends Controller
{
    /**
     * Detalle de la sesión para el coachee autenticado.
     * - Bloquea DRAFT.
     * - Carga relaciones mínimas para la vista.
     * - Usa MÉTRICAS PERSISTIDAS en evaluation_sessions:
     *      overall_avg (0..1), answered_avg (0..1), total_score, max_score
     *   y los accessors %: overall_avg_pct, answered_avg_pct.
     */
    public function show(EvaluationSession $session): View|RedirectResponse
    {
        $this->ensureOwnership($session);

        if ($session->status === EvaluationSession::STATUS_DRAFT) {
            return redirect()
                ->route('coachee.sessions.index')
                ->with('warning', 'Esta evaluación está en borrador y aún no está disponible.');
        }

        $session->load([
            'guideResponses.guideTemplate', // para nombres de guías
            'signatures',
            'evaluator',
            'participant',
        ]);

        // Promedio por guía: usamos el agregado PERSISTIDO en cada GuideResponse (total_score 0..100)
        $guideScores = $session->guideResponses
            ->mapWithKeys(fn($gr) => [$gr->id => (float) ($gr->total_score ?? 0.0)])
            ->all();

        // Promedios globales (persistidos) de la sesión
        $overallAvg     = $session->overall_avg;      // 0..1 o null
        $overallAvgPct  = $session->overall_avg_pct;  // 0..100 o null
        $answeredAvg    = $session->answered_avg;     // 0..1 o null
        $answeredAvgPct = $session->answered_avg_pct; // 0..100 o null
        $maxScore       = (float) $session->max_score;
        $totalScore     = (float) $session->total_score;

        return view('coachee.sessions.show', compact(
            'session',
            'guideScores',
            'overallAvg',
            'overallAvgPct',
            'answeredAvg',
            'answeredAvgPct',
            'maxScore',
            'totalScore'
        ));
    }

    /**
     * Formulario de firma del coachee.
     * Regla: solo si el modelo indica que el coachee “necesita firmar”.
     */
    public function signForm(EvaluationSession $session): View|RedirectResponse
    {
        $this->ensureOwnership($session);

        if (! $session->needsSignatureFrom(Auth::id(), 'coachee')) {
            return redirect()
                ->route('coachee.sessions.show', $session)
                ->with('warning', 'Esta sesión no está lista para tu firma.');
        }

        $session->load(['signatures']);

        return view('coachee.sessions.sign', ['session' => $session]);
    }

    /**
     * Guarda la firma del coachee (PNG base64 o ruta).
     * Transición: si procede, la sesión pasa a COMPLETED (segunda firma).
     */
    public function sign(Request $request, EvaluationSession $session): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            'signature' => ['required', 'string'], // dataURL base64 o ruta
            'method'    => ['nullable', 'string', 'max:50'],
        ]);

        if (! $session->needsSignatureFrom(Auth::id(), 'coachee')) {
            return redirect()
                ->route('coachee.sessions.show', $session)
                ->with('warning', 'No puedes firmar esta sesión.');
        }

        $signaturePath = $this->storeSignatureImage($data['signature'], $session->id);

        $session->signatures()->updateOrCreate(
            ['user_id' => Auth::id(), 'signer_role' => 'coachee'],
            [
                'status'            => 'signed',
                'signed_at'         => now(),
                'digital_signature' => $signaturePath,
                'method'            => $data['method'] ?? 'drawn',
                'rejection_reason'  => null,
            ],
        );

        // Al firmar el coachee, la sesión queda COMPLETED
        $session->update(['status' => EvaluationSession::STATUS_COMPLETED]);

        return redirect()
            ->route('coachee.sessions.show', $session)
            ->with('success', '¡Tu firma fue registrada correctamente!');
    }

    /**
     * Rechazo de firma por el coachee.
     * Política: vuelve la sesión a PENDING para correcciones.
     */
    public function reject(Request $request, EvaluationSession $session): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if (! in_array($session->status, [
            EvaluationSession::STATUS_SIGNED,     // esperando firma del coachee
            EvaluationSession::STATUS_COMPLETED,  // (opcional) permitir revertir por error detectado
        ], true)) {
            return redirect()
                ->route('coachee.sessions.show', $session)
                ->with('warning', 'Esta sesión no puede ser rechazada en su estado actual.');
        }

        $session->signatures()->updateOrCreate(
            ['user_id' => Auth::id(), 'signer_role' => 'coachee'],
            [
                'status'            => 'rejected',
                'signed_at'         => null,
                'digital_signature' => null,
                'method'            => 'reject',
                'rejection_reason'  => $data['reason'],
            ],
        );

        $session->update(['status' => EvaluationSession::STATUS_PENDING]);

        return redirect()
            ->route('coachee.sessions.show', $session)
            ->with('success', 'Has rechazado la firma. Se solicitarán correcciones.');
    }

    /**
     * Descarga del PDF de la sesión (solo COMPLETED).
     */
public function download(EvaluationSession $session): StreamedResponse|RedirectResponse
{
    $this->ensureOwnership($session);

    if ($session->status !== EvaluationSession::STATUS_COMPLETED) {
        return redirect()
            ->route('coachee.sessions.show', $session)
            ->with('warning', 'La sesión debe estar COMPLETED (ambas firmas) para descargar el PDF.');
    }

    if (! $session->pdf_path || ! Storage::disk('local')->exists($session->pdf_path)) {
        return redirect()
            ->route('coachee.sessions.show', $session)
            ->with('warning', 'El PDF aún no está disponible. Intenta más tarde.');
    }

    // Opción 1 (preferida):
    return Storage::download($session->pdf_path, "evaluacion-{$session->id}.pdf");

    // O Opción 2:
    // return response()->download(storage_path('app/'.$session->pdf_path), "evaluacion-{$session->id}.pdf");
}

    /**
     * Acceso por token (desde email).
     * Usa la misma regla centralizada del modelo para determinar si puede firmar.
     */
    public function signByToken(string $token): RedirectResponse
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw_if($e instanceof DecryptException);
            abort(404, 'Token inválido.');
        }

        if (! is_array($payload) || ! isset($payload['session_id'], $payload['user_id'], $payload['exp'])) {
            abort(404, 'Token mal formado.');
        }

        if (now()->timestamp > (int) $payload['exp']) {
            abort(410, 'El enlace de firma ha expirado.');
        }

        if ((int) $payload['user_id'] !== (int) Auth::id()) {
            abort(403, 'No tienes permisos para firmar esta sesión.');
        }

        /** @var EvaluationSession $session */
        $session = EvaluationSession::query()->findOrFail((int) $payload['session_id']);

        $this->ensureOwnership($session);

        // Usa la misma regla del modelo (ventana de firma para coachee)
        if (! $session->needsSignatureFrom(Auth::id(), 'coachee')) {
            return redirect()
                ->route('coachee.sessions.show', $session)
                ->with('warning', 'Aún no puedes firmar esta sesión.');
        }

        return redirect()->route('coachee.sessions.sign.form', $session);
    }

    // =========================
    // Helpers privados
    // =========================

    /**
     * Verifica que la sesión pertenezca al coachee autenticado.
     */
    private function ensureOwnership(EvaluationSession $session): void
    {
        if ((int) $session->participant_id !== (int) Auth::id()) {
            abort(403, 'No tienes permisos para acceder a esta sesión.');
        }
    }

    /**
     * Guarda la firma (dataURL o ruta existente) y devuelve la ruta relativa en storage (disk=public).
     */
    private function storeSignatureImage(string $input, int $sessionId): ?string
    {
        if (str_starts_with($input, 'data:image')) {
            [$meta, $content] = explode(',', $input, 2);
            $binary = base64_decode($content, true);

            if ($binary === false) {
                abort(422, 'No se pudo decodificar la firma.');
            }

            $path = "signatures/sessions/{$sessionId}/coachee.png";
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        return $input ?: null;
    }
}
