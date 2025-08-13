<?php

namespace App\Livewire\Coach;

use App\Models\Scale;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\EvaluationSession;
use App\Models\GuideItemResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\EvaluationSessionSignature;
use App\Services\ScoreAggregator;
use Livewire\Attributes\Computed;
use App\Models\TemplateItem as TI;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class EvaluationSessionFilling extends Component
{
    public EvaluationSession $session;
    public int $currentTab = 0;
    public array $answers = [];
    public bool $showSignatureModal = false;

    public function mount(EvaluationSession $session): void
    {
        abort_if(
            $session->evaluator_id !== Auth::id()
                || in_array($session->status, [
                    EvaluationSession::STATUS_COMPLETED,
                    EvaluationSession::STATUS_CANCELLED,
                ], true),
            403
        );

        $this->session = $session->load([
            'participant',
            'guideResponses.guideTemplate.sections.items',
            'guideResponses.itemResponses',
            'guideResponses.itemResponses.item',
            'signatures',
        ]);

        foreach ($this->session->guideResponses as $gr) {
            $this->answers[$gr->id] = [];
            foreach ($gr->itemResponses as $resp) {
                $itemType = $resp->item->type ?? 'text';
                $answer   = (array) ($resp->answer ?? []);

                // Todos los tipos ahora usan 'value' de manera unificada
                $this->answers[$gr->id][$resp->template_item_id]['value'] = $answer[0]['value'] ?? '';
            }
        }
    }

    public function setTab(int $index): void
    {
        $this->currentTab = $index;
    }

    public function save(): void
    {
        if (in_array($this->session->status, [EvaluationSession::STATUS_SIGNED, EvaluationSession::STATUS_COMPLETED], true)) {
            $this->dispatch('toast', type: 'error', message: 'La sesión ya fue firmada o completada.', timer: 4000);
            return;
        }

        // Usaremos un objeto para mantener el estado mutable
        $state = new class {
            public bool $touched = false;
            public array $updatedResponseIds = [];
        };

        DB::transaction(function () use ($state) {
            foreach ($this->session->guideResponses as $gr) {
                $responseUpdated = false;

                foreach ($gr->guideTemplate->sections as $section) {
                    foreach ($section->items as $item) {
                        $hasInput = array_key_exists($item->id, $this->answers[$gr->id] ?? []);
                        if (!$hasInput) {
                            continue;
                        }

                        $raw = $this->answers[$gr->id][$item->id];
                        // Todos los tipos usan estructura unificada [{'value': 'X'}]
                        $value = is_array($raw) ? ($raw['value'] ?? '') : $raw;
                        $answerArray = [['value' => $value]];
                        
                        // Calcular score según el tipo
                        switch ($item->type) {
                            case TI::TYPE_TEXT:
                                $score = 0.0; // Text no genera puntaje
                                break;
                            case TI::TYPE_SELECT:
                            case TI::TYPE_SCALE:
                            case TI::TYPE_RADIO:
                                $score = is_numeric($value) ? (float) $value : 0.0;
                                break;
                            default:
                                $score = 0.0;
                        }

                        $updatedResponse = GuideItemResponse::updateOrCreate(
                            [
                                'guide_response_id' => $gr->id,
                                'template_item_id'  => $item->id,
                            ],
                            [
                                'answer'         => $answerArray,
                                'score_obtained' => $score,
                            ]
                        );

                        if ($updatedResponse->wasRecentlyCreated || $updatedResponse->wasChanged()) {
                            $state->touched = true;
                            $responseUpdated = true;
                        }
                    }
                }

                if ($responseUpdated) {
                    $state->updatedResponseIds[] = $gr->id;
                }
            }

            if ($state->touched && $this->session->status !== EvaluationSession::STATUS_IN_PROGRESS) {
                $this->session->update(['status' => EvaluationSession::STATUS_IN_PROGRESS]);
            }
        });

        // Actualizar componentes después de la transacción
        if (!empty($state->updatedResponseIds)) {
            $this->dispatch('answers-updated', responseIds: $state->updatedResponseIds);
            $this->dispatch('refreshScores');
            $this->dispatch('$refresh');
        }

        $this->dispatch('toast', type: 'success', message: 'Respuestas guardadas correctamente.', timer: 4000);
    }

    public function isComplete(int $guideResponseId): bool
    {
        $gr = $this->session->guideResponses->firstWhere('id', $guideResponseId);
        if (! $gr) return false;

        $totalItems = $gr->guideTemplate->sections->sum(fn($s) => $s->items->count());
        $answered   = count($this->answers[$guideResponseId] ?? []);

        return $totalItems > 0 && $answered >= $totalItems;
    }

    public function allComplete(): bool
    {
        return $this->session->guideResponses->every(
            fn($gr) => $this->isComplete($gr->id)
        );
    }

    public function goToSignature(): void
    {
        if (! $this->allComplete()) {
            $this->dispatch('toast', type: 'error', message: 'Debes completar todas las guías antes de firmar.', timer: 4000);
            return;
        }

        $this->showSignatureModal = true;
        $this->dispatch('signatureModalOpen');
    }

    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
        $this->dispatch('signatureModalClosed');
    }

    #[On('saveSignature')]
    public function handleSaveSignature(string $signatureDataUrl): void
    {
        if (! $signatureDataUrl || ! str_starts_with($signatureDataUrl, 'data:image/png;base64,')) {
            $this->dispatch('toast', type: 'error', message: 'Firma inválida.', timer: 4000);
            return;
        }

        if (strlen($signatureDataUrl) > 4_000_000) {
            $this->dispatch('toast', type: 'error', message: 'La firma es demasiado grande.', timer: 4000);
            return;
        }

        if (in_array($this->session->status, [EvaluationSession::STATUS_SIGNED, EvaluationSession::STATUS_COMPLETED], true)) {
            $this->dispatch('toast', type: 'error', message: 'La sesión ya fue firmada o completada.', timer: 4000);
            return;
        }

        try {
            DB::transaction(function () use ($signatureDataUrl) {
                [, $encoded] = explode(',', $signatureDataUrl, 2);
                $binary = base64_decode($encoded, true);
                if ($binary === false) {
                    throw new \RuntimeException('Error al decodificar la firma.');
                }
                $user = Auth::user();

                $filename = 'signature_' . $this->session->id . '_' . Str::random(8) . '.png';
                $path = "signatures/{$user->id}/{$filename}";
                Storage::put($path, $binary);

                $signerRole = match (true) {
                    $user->id === $this->session->evaluator_id  => 'coach',
                    $user->id === $this->session->participant_id => 'coachee',
                    default => 'approver',
                };

                $sig = EvaluationSessionSignature::updateOrCreate(
                    [
                        'session_id'  => $this->session->id,
                        'user_id'     => $user->id,
                        'signer_role' => $signerRole,
                    ],
                    [
                        'signed_at'         => now(),
                        'digital_signature' => $path,
                        'method'            => 'canvas',
                        'status'            => EvaluationSession::STATUS_SIGNED,
                    ]
                );

                // 🔄 Recalcular totales/avg persistidos (por si hubo cambios antes de firmar)
                app(ScoreAggregator::class)->recalcSession($this->session->load('guideResponses'));

                $signatureCount = $this->session->signatures()->where('status', EvaluationSession::STATUS_SIGNED)->count();
                $newStatus = $signatureCount >= 2
                    ? EvaluationSession::STATUS_COMPLETED
                    : EvaluationSession::STATUS_SIGNED;

                $this->session->update(['status' => $newStatus]);

                if ($signerRole === 'coach' && $sig->wasRecentlyCreated) {
                    $ttlDays = (int) config('coaching.sign_link_ttl_days', 7);
                    $payload = json_encode([
                        'session_id' => $this->session->id,
                        'user_id'    => $this->session->participant_id,
                        'exp'        => now()->addDays($ttlDays)->timestamp,
                    ], JSON_UNESCAPED_SLASHES);

                    $token = Crypt::encryptString($payload);
                    $url   = route('coachee.sessions.sign.token', $token);

                    optional($this->session->participant)->notify(
                        new \App\Notifications\PendingSignatureNotification($this->session, $url)
                    );
                }
            });

            $this->showSignatureModal = false;
            $this->session->refresh()->load('signatures');
            $this->dispatch('toast', type: 'success', message: 'Firma guardada correctamente.', timer: 4000);
            $this->redirect(route('evaluation.summary', [$this->session]));
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error guardando la firma: ' . $e->getMessage(), timer: 6000);
        }
    }

    #[Computed]
    public function scales()
    {
        return Scale::query()->get(['id', 'label', 'value']);
    }

    #[Computed]
    public function readOnly(): bool
    {
        return in_array(
            $this->session->status,
            [EvaluationSession::STATUS_SIGNED, EvaluationSession::STATUS_COMPLETED],
            true
        );
    }

    public function render()
    {
        return view('livewire.coach.evaluation-session-filling');
    }
}
