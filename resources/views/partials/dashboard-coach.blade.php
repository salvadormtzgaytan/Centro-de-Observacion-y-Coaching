<h2 class="mb-4 mt-8 text-xl font-semibold text-gray-700 dark:text-gray-100">
    Funciones como Coach
</h2>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
    <x-cards.card title="{{ __('general.new_evaluation') }}" route="{{ route('evaluation.create') }}"
        icon="heroicon-o-plus-circle" description="Inicia una nueva observación o sesión de coaching" />

    <x-cards.card title="{{ __('general.in_progress_evaluation') }}" route="{{ route('evaluation.index') }}"
        icon="heroicon-o-document-text" description="Edita las evaluaciones que aún están en borrador"
        :count="$draftCount ?? null" />

    <x-cards.card title="{{ __('general.evaluation_history') }}" route="{{ route('evaluation.history') }}"
        icon="heroicon-o-archive-box" description="Consulta tus evaluaciones ya completadas o firmadas" />
</div>
