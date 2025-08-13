<?php

namespace App\Observers;

use App\Models\GuideItemResponse;
use App\Services\ScoreAggregator;
use Illuminate\Support\Facades\DB;

class GuideItemResponseObserver
{
    public bool $afterCommit = true;

    public function __construct(private ScoreAggregator $svc) {}

    /**
     * Handle the GuideItemResponse "created" event.
     */
    public function created(GuideItemResponse $guideItemResponse): void
    {
        $this->processResponse($guideItemResponse);
    }

    /**
     * Handle the GuideItemResponse "updated" event.
     */
    public function updated(GuideItemResponse $guideItemResponse): void
    {
        $this->processResponse($guideItemResponse);
    }

    /**
     * Handle the GuideItemResponse "deleted" event.
     */
    public function deleted(GuideItemResponse $guideItemResponse): void
    {
        $this->processResponse($guideItemResponse);
    }

    /**
     * Handle the GuideItemResponse "restored" event.
     */
    public function restored(GuideItemResponse $guideItemResponse): void
    {
        $this->processResponse($guideItemResponse);
    }

    /**
     * Handle the GuideItemResponse "force deleted" event.
     */
    public function forceDeleted(GuideItemResponse $guideItemResponse): void
    {
        $this->processResponse($guideItemResponse);
    }

    /**
     * Procesa uniformemente todos los eventos de GuideItemResponse
     *
     * @param GuideItemResponse $guideItemResponse
     * @return void
     */
    private function processResponse(GuideItemResponse $guideItemResponse): void
    {
        DB::transaction(function () use ($guideItemResponse) {
            // Obtener la GuideResponse relacionada (incluyendo soft deleted)
            $guideResponse = $guideItemResponse->response()->withTrashed()->first();

            if ($guideResponse) {
                // 1. Recalcular la respuesta individual
                $this->svc->recalcGuideResponse($guideResponse);

                // 2. Recalcular la sesión completa
                $session = $guideResponse->session()->withTrashed()->first();
                if ($session) {
                    // Cargar todas las respuestas (incluyendo soft deleted)
                    $session->load(['guideResponses' => function ($query) {
                        $query->withTrashed();
                    }]);

                    $this->svc->recalcSession($session);
                }
            }
        });
    }
}
