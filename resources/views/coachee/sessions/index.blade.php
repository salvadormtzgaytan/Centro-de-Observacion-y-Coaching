{{-- resources/views/coachee/sessions/index.blade.php --}}
<x-layouts.app :title="__('Mis Evaluaciones')">
    <livewire:coachee.sessions-index :initialFilter="request('filter')" />
</x-layouts.app>
