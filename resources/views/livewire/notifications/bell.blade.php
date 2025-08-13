<div wire:poll.30s class="relative">
    <flux:dropdown>
        <flux:button
            variant="ghost"
            icon="bell"
            class="relative"
            color="gray"
        >
            @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1 inline-flex items-center justify-center
                             rounded-full text-[10px] font-bold px-1.5 py-0.5 bg-red-600 text-white">
                    {{ $unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80 max-h-96 overflow-y-auto">
            <div class="px-3 py-2 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                    Notificaciones
                </span>
                @if($unreadCount > 0)
                    <flux:button size="xs" variant="link" wire:click="markAllAsRead">
                        Marcar todas como leídas
                    </flux:button>
                @endif
            </div>

            <flux:separator />

            @forelse($latest as $n)
                <div class="px-3 py-2">
                    <div class="flex items-start gap-2">
                        <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4 mt-0.5 text-gray-500" />
                        <div class="flex-1">
                            <div class="text-sm text-gray-800 dark:text-gray-100">
                                {{ data_get($n->data, 'message', 'Tienes una notificación.') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $n->created_at->diffForHumans() }}
                            </div>
                            <div class="mt-2 flex gap-2">
                                @if(data_get($n->data, 'url'))
                                    <flux:button size="xs" wire:click="goTo('{{ $n->id }}')" icon="arrow-right-end-on-rectangle">
                                        Abrir
                                    </flux:button>
                                @endif
                                @if(is_null($n->read_at))
                                    <flux:button size="xs" variant="ghost" wire:click="markAsRead('{{ $n->id }}')">
                                        Marcar leída
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <flux:separator />
            @empty
                <div class="px-3 py-6 text-center text-sm text-gray-500">
                    Sin notificaciones.
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
