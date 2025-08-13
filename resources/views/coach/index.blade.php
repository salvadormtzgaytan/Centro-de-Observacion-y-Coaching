<x-layouts.app :title="__('general.in_progress_evaluation')">
  <div class="px-6 py-8">
    <h1 class="mb-6 text-2xl font-semibold text-gray-800 dark:text-white">
      {{ __('general.in_progress_evaluation') }}
    </h1>

    @if ($sessions->isEmpty())
      <p class="text-gray-500 dark:text-gray-400">
        {{ __('general.no_active_sessions') }}
      </p>
    @else
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
        @foreach ($sessions as $session)
          <x-cards.session-card :session="$session">
            {{-- SLOT DE ACCIONES --}}
            <div class="flex items-center gap-2">
              <flux:button href="{{ route('evaluation.fill', $session) }}" icon:trailing="arrow-up-right"
                variant="primary">
                {{ __('general.show') }}
              </flux:button>
              @can('delete', $session)
                <flux:modal.trigger name="delete-session-{{ $session->id }}">
                  <flux:button icon="trash" title="{{ __('general.delete') }}" variant="danger">
                    {{ __('general.delete') }}
                  </flux:button>
                </flux:modal.trigger>
              @endcan
            </div>
            {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
            @can('delete', $session)
              <flux:modal class="min-w-[22rem]" name="delete-session-{{ $session->id }}">
                <div class="space-y-6">
                  <div>
                    <flux:heading size="lg">{{ __('general.confirm_delete_title') }}</flux:heading>
                    <flux:text class="mt-2">
                      <p class="text-red-600">{{ __('general.confirm_delete_body') }}</p>
                    </flux:text>
                  </div>
                  <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                      <flux:button color="zinc" variant="primary">{{ __('general.cancel') }}</flux:button>
                    </flux:modal.close>
                    <form action="{{ route('evaluation.destroy', $session) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <flux:button icon="trash" type="submit" variant="danger">
                        {{ __('general.confirm_delete_ok') }}
                      </flux:button>
                    </form>
                  </div>
                </div>
              </flux:modal>
            @endcan
          </x-cards.session-card>
        @endforeach
      </div>

      <div class="mt-8">
        {{ $sessions->links() }}
      </div>
    @endif
  </div>
</x-layouts.app>
