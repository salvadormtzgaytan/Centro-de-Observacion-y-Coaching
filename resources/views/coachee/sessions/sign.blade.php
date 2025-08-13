{{-- resources/views/coachee/sessions/sign.blade.php --}}
<x-layouts.app>
  @vite('resources/js/components/sign-coachee.js')

  <div class="co-page">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="co-title">Firmar evaluación #{{ $session->id }}</h1>
        <p class="text-sm text-slate-600 dark:text-slate-300">
          {{ $session->cycle_full_name ?? ($session->cycle ?? '—') }} ·
          {{ $session->date?->format('d/m/Y') ?? '—' }}
        </p>
      </div>

      <flux:button as="a" href="{{ route('coachee.sessions.show', $session) }}" icon="arrow-left" variant="ghost">
        Volver
      </flux:button>
    </div>

    @if ($errors->any())
      <div class="mt-3 rounded-md bg-rose-50 p-3 text-rose-700 dark:bg-rose-900/20 dark:text-rose-200">
        Hubo un problema al enviar tu firma. Revisa e inténtalo de nuevo.
      </div>
    @endif
    @if (session('warning'))
      <div class="mt-3 rounded-md bg-amber-50 p-3 text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
        {{ session('warning') }}
      </div>
    @endif

    <div class="co-section mt-4">
      <form action="{{ route('coachee.sessions.sign', $session) }}" id="sign-form" method="POST">
        @csrf
        <input id="signature-input" name="signature" type="hidden">
        <input name="method" type="hidden" value="drawn">

        <div class="text-xs uppercase text-slate-500 dark:text-slate-400">Tu firma</div>

        <div class="mt-2 rounded border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
          <div class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
            Traza tu firma dentro del recuadro. Usa un trazo continuo si es posible.
          </div>
          <div class="px-3 pb-3">
            <div class="signature-box h-48">
              <canvas class="block h-48 w-full rounded bg-white dark:bg-slate-900" id="signature-canvas"
                style="touch-action:none">
              </canvas>
            </div>
          </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
          <flux:button icon="trash" id="btn-clear" type="button" variant="subtle">Limpiar</flux:button>

          <div class="ml-auto flex items-center gap-2">
            <flux:button as="a" href="{{ route('coachee.sessions.show', $session) }}" variant="ghost">
              Cancelar
            </flux:button>
            <flux:button disabled icon="check" id="btn-save" type="button" variant="primary">
              Firmar
            </flux:button>
          </div>
        </div>

        {{-- Vista previa opcional --}}
        <div class="mt-4 hidden" id="sig-preview-wrap">
          <div class="mb-1 text-xs uppercase text-slate-500 dark:text-slate-400">Vista previa</div>
          <img alt="Vista previa de firma" class="signature-img" id="sig-preview" />
        </div>
      </form>
    </div>
  </div>
</x-layouts.app>
