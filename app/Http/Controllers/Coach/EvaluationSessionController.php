<?php

namespace App\Http\Controllers\Coach;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\EvaluationSession;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EvaluationSessionController extends Controller
{
    /**
     * Lista los borradores y en progreso del evaluador autenticado.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', EvaluationSession::class);

        $user = $request->user();

        $sessions = EvaluationSession::query()
            ->with([
                'participant:id,name',
                'guideResponses.guideTemplate:id,name',
            ])
            ->with([
                'participant:id,name,email,profile_photo_path',
                'division:id,name',
                'guideResponses:id,session_id,guide_template_id,total_score',
            ])
            ->withCount([
                'signatures as signed_signatures_count' => fn($q) => $q->where('status', 'signed'),
            ])

            ->where('evaluator_id', $user->id)
            ->whereIn('status', [
                EvaluationSession::STATUS_DRAFT,
                EvaluationSession::STATUS_IN_PROGRESS,
                EvaluationSession::STATUS_PENDING,
                EvaluationSession::STATUS_SIGNED
            ])
            ->latest('date')
            ->latest('id')
            ->paginate(6);

        return view('coach.index', compact('sessions'));
    }

    /**
     * Elimina una sesión nunca si está signed.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $session = EvaluationSession::query()
                ->whereKey($id)
                ->whereNotIn('status', [
                    EvaluationSession::STATUS_COMPLETED
                ])
                ->firstOrFail();

            // Policy (Shield) — delete
            Gate::authorize('delete', $session);

            $session->delete(); // Soft delete

            activity()
                ->causedBy($user)
                ->performedOn($session)
                ->withProperties([
                    'status' => $session->status,
                    'as'     => 'super_admin',
                ])
                ->log('Sesión de evaluación eliminada');

            Alert::toast('Sesión eliminada correctamente.', 'success')
                ->persistent(false)
                ->autoClose(4000);

            return redirect()->route('evaluation.index');
        } catch (ModelNotFoundException $e) {
            Alert::toast(
                'No puedes eliminar esta sesión (no existe o ya está completada/firmada).',
                'error'
            )->persistent(false)->autoClose(4000);

            return redirect()->route('evaluation.index');
        } catch (\Throwable $e) {
            Alert::toast('Ocurrió un error al eliminar la sesión. Intenta nuevamente.', 'error')
                ->persistent(false)->autoClose(4000);

            return redirect()->route('evaluation.index');
        }
    }

    public function summary(Request $request, EvaluationSession $session): View | RedirectResponse
    {
        Gate::authorize('viewAny', EvaluationSession::class);
        $this->ensureOwnership($session);
        if ($session->status === EvaluationSession::STATUS_DRAFT) {
            return redirect()
                ->route('evaluation.history')
                ->with('warning', 'Esta evaluación aún no está disponible.');
        }
        $user = $request->user();

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

        return view('coach.summary', compact(
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

    private function ensureOwnership(EvaluationSession $session): void
    {
        if ((int) $session->evaluator_id !== (int) Auth::id()) {
            abort(403, 'No tienes permisos para acceder a esta sesión.');
        }
    }
}
