<?php

namespace App\Observers;

use App\Models\TemplateItem;

class TemplateItemObserver
{
    /**
     * Se ejecuta ANTES de crear o actualizar.
     * Aplica para ambos casos en un solo lugar.
     */
    private function saving(TemplateItem $item): void
    {
        if (in_array($item->type, ['select', 'radio', 'scale'], true)) {
            $scales = $item->getScales();
            $item->options = $scales['options'];
            $item->help_text = $scales['help'];
        } else {
            $item->options = null;
            $item->help_text = $item->help_text ?: null;
        }
    }

    // (Opcional) Si quieres ganchos post‑persistencia:
    public function created(TemplateItem $item): void {}

    public function updated(TemplateItem $item): void {}

    public function restored(TemplateItem $item): void {}

    public function deleted(TemplateItem $item): void {}

    public function forceDeleted(TemplateItem $item): void {}
}
