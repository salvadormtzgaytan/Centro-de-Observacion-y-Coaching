<h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100 mt-8 mb-4">
    Funciones como Evaluado
</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <x-cards.card
        title="Mis Evaluaciones"
        route="{{ route('coachee.sessions.index') }}"
        icon="heroicon-o-clipboard-document-check"
        description="Historial de observaciones o coaching recibidas"
        :count="$pendingSignatures ?? null"
    />
</div>


