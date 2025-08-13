<?php

namespace App\Observers;

use App\Models\GuideResponse;
use App\Services\ScoreAggregator;
use Illuminate\Support\Facades\DB;

class GuideResponseObserver
{
    public bool $afterCommit = true;

    public function __construct(private ScoreAggregator $svc) {}

    /**
     * Handle the GuideResponse "created" event.
     */
    public function created(GuideResponse $guideResponse): void
    {
        $this->recalcAll($guideResponse);
    }

    /**
     * Handle the GuideResponse "updated" event.
     */
    public function updated(GuideResponse $guideResponse): void
    {
        $this->recalcAll($guideResponse);
    }

    /**
     * Handle the GuideResponse "deleted" event.
     */
    public function deleted(GuideResponse $guideResponse): void
    {
        $this->recalcSessionOnly($guideResponse);
    }

    /**
     * Handle the GuideResponse "restored" event.
     */
    public function restored(GuideResponse $guideResponse): void
    {
        $this->recalcAll($guideResponse);
    }

    /**
     * Handle the GuideResponse "force deleted" event.
     */
    public function forceDeleted(GuideResponse $guideResponse): void
    {
        $this->recalcSessionOnly($guideResponse);
    }

    /**
     * Recalcula tanto la respuesta como la sesión completa
     *
     * @param GuideResponse $guideResponse
     * @return void
     */
    private function recalcAll(GuideResponse $guideResponse): void
    {
        DB::transaction(function () use ($guideResponse) {
            // 1. Recalcular la respuesta individual
            $this->svc->recalcGuideResponse($guideResponse);

            // 2. Recalcular la sesión completa
            $this->recalcSession($guideResponse);
        });
    }

    /**
     * Recalcula solo la sesión (para eventos de eliminación)
     *
     * @param GuideResponse $guideResponse
     * @return void
     */
    private function recalcSessionOnly(GuideResponse $guideResponse): void
    {
        DB::transaction(function () use ($guideResponse) {
            $this->recalcSession($guideResponse);
        });
    }

    /**
     * Método común para recalcular la sesión
     *
     * @param GuideResponse $guideResponse
     * @return void
     */
    private function recalcSession(GuideResponse $guideResponse): void
    {
        $session = $guideResponse->session()->withTrashed()->first();

        if ($session) {
            // Cargar todas las respuestas (incluyendo soft deleted)
            $session->load(['guideResponses' => function ($query) {
                $query->withTrashed();
            }]);

            $this->svc->recalcSession($session);
        }
    }
}
