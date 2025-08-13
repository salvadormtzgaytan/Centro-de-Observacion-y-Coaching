<x-layouts.app :title="__('general.dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @php
            $user = auth()->user();
        @endphp

        {{-- Módulos según roles combinados --}}
        @if ($user->hasRole('super_admin'))
            @include('partials.dashboard-superadmin')
        @endif

        @if ($user->hasRole('administrador'))
            @include('partials.dashboard-admin')
        @endif

        @if ($user->hasRole('coach'))
            @include('partials.dashboard-coach')
        @endif

        @if ($user->hasRole('coachee'))
            @include('partials.dashboard-coachee')
        @endif

        {{-- Espacio para widgets o analíticas futuras --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 mt-8">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
