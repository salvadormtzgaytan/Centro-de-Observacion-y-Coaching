<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>
        @php $user = auth()->user(); @endphp
        {{-- Enlaces para Super Admin --}}
        @if ($user->hasRole('super_admin') || $user->hasRole('administrador'))
            <flux:navlist.group :heading="__('general.admin_panel')" expandable>
                <flux:navlist.item icon="cog-6-tooth" :href="route('filament.admin.pages.dashboard')"
                    :current="request()->is('admin*')" wire:navigate>{{ __('general.admin_panel') }}</flux:navlist.item>
            </flux:navlist.group>
        @endif

        {{-- Enlaces para Coachee --}}
        @if ($user->hasRole('coachee'))
            <flux:navlist.group :heading="__('general.coachee.desc_menu')" expandable>
                {{--                 <flux:navlist.item
                    icon="pencil-square"
                    :href="route('coachee.sessions.index', ['filter' => 'pending'])"
                    :current="request()->fullUrlIs(route('coachee.sessions.index', ['filter' => 'pending']))"
                    wire:navigate
                >
                    {{ __('general.pending_signatures') }}
                </flux:navlist.item> --}}

                <flux:navlist.item icon="clipboard-document-check" :href="route('coachee.sessions.index')"
                    :current="request()->routeIs('coachee.sessions.index')" wire:navigate class="truncate">
                    {{ __('general.evaluation_history') }}
                </flux:navlist.item>
            </flux:navlist.group>
        @endif

        {{-- Enlaces para Coach --}}
        @if ($user->hasRole('coach'))
            <flux:navlist.group :heading="__('general.evaluation.desc_menu')" expandable>

                <flux:navlist.item icon="plus-circle" :href="route('evaluation.create')"
                    :current="request()->routeIs('evaluation.create')" wire:navigate class="truncate">
                    {{ __('general.new_evaluation') }}
                </flux:navlist.item>

                <flux:navlist.item icon="document-text" :href="route('evaluation.index')"
                    :current="request()->routeIs('evaluation.index')" wire:navigate class="truncate">
                    {{ __('general.in_progress_evaluation') }}
                </flux:navlist.item>

                <flux:navlist.item icon="archive-box" :href="route('evaluation.history')"
                    :current="request()->routeIs('evaluation.history')" wire:navigate class="truncate">
                    {{ __('general.evaluation_history') }}
                </flux:navlist.item>

            </flux:navlist.group>
        @endif
        <flux:spacer />

        <!-- <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('general.repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('general.documentation') }}
            </flux:navlist.item>
        </flux:navlist> -->

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile circle :name="auth()->user()->name" :avatar="auth()->user()->getProfilePhotoUrlAttribute()"
                icon-trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <!-- Sección de perfil -->
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <!-- Toggle de tema - Versión perfectamente alineada -->
                <flux:menu.item x-data as="div" class="cursor-pointer" data-flux-menu-item-has-icon
                    @click.stop="$flux.appearance = ($flux.appearance === 'light' ? 'dark' : 'light')"
                    style="width: 0.25rem !important; padding-left: 0 !important; padding-right: 0 !important;">
                    <div class="flex items-center gap-3 px-1 py-1.5">
                        <div class="flex h-8 w-8 items-center justify-center">
                            <flux:icon x-show="$flux.appearance === 'light'" name="sun" class="h-5 w-5" />
                            <flux:icon x-show="$flux.appearance !== 'light'" name="moon" class="h-5 w-5" />
                        </div>
                        <div class="flex-1 text-start">
                            <span
                                x-text="$flux.appearance === 'light' ? '{{ __('Light') }}' : '{{ __('Dark') }}'"></span>
                        </div>
                        <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            x-bind:class="{
                                'bg-zinc-700': $flux.appearance === 'light',
                                'bg-white': $flux.appearance === 'dark'
                            }">
                            <span class="sr-only">Toggle theme</span>
                            <span
                                x-bind:class="{
                                    'translate-x-6 bg-zinc-700': $flux.appearance === 'dark',
                                    'translate-x-1 bg-white': $flux.appearance === 'light'
                                }"
                                class="inline-block h-4 w-4 transform rounded-full transition">
                            </span>
                        </div>
                    </div>
                </flux:menu.item>

                <!-- Configuración -->
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                    {{ __('general.settings') }}
                </flux:menu.item>

                <flux:menu.separator />

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('general.logout') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile circle :name="auth()->user()->initials()"
                :avatar="auth()->user()->getProfilePhotoUrlAttribute()" icon-trailing="chevron-down" />

            <flux:menu>
                <!-- Sección de perfil -->
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <!-- Toggle de tema mobile - Versión perfectamente alineada -->
                <flux:menu.item x-data as="div" class="cursor-pointer" data-flux-menu-item-has-icon
                    @click.stop="$flux.appearance = ($flux.appearance === 'light' ? 'dark' : 'light')"
                    style="width: 0.25rem !important; padding-left: 0 !important; padding-right: 0 !important;">
                    <div class="flex items-center gap-3 px-1 py-1.5">
                        <div class="flex h-8 w-8 items-center justify-center">
                            <flux:icon x-show="$flux.appearance === 'light'" name="sun" class="h-5 w-5" />
                            <flux:icon x-show="$flux.appearance !== 'light'" name="moon" class="h-5 w-5" />
                        </div>
                        <div class="flex-1 text-start">
                            <span
                                x-text="$flux.appearance === 'light' ? '{{ __('Light') }}' : '{{ __('Dark') }}'"></span>
                        </div>
                        <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            x-bind:class="{
                                'bg-zinc-700': $flux.appearance === 'light',
                                'bg-white': $flux.appearance === 'dark'
                            }">
                            <span class="sr-only">Toggle theme</span>
                            <span
                                x-bind:class="{
                                    'translate-x-6 bg-zinc-700': $flux.appearance === 'dark',
                                    'translate-x-1 bg-white': $flux.appearance === 'light'
                                }"
                                class="inline-block h-4 w-4 transform rounded-full transition">
                            </span>
                        </div>
                    </div>
                </flux:menu.item>

                <!-- Configuración -->
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                    {{ __('general.settings') }}
                </flux:menu.item>

                <flux:menu.separator />

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('general.logout') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}
    @include('sweetalert::alert')
    @livewireScripts
    @fluxScripts
    @stack('scripts')

</body>

</html>
