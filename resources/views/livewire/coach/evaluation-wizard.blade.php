{{-- resources/views/livewire/coach/evaluation-wizard.blade.php
====================================================================
WIZARD DE CREACIÓN DE EVALUACIÓN – DOCUMENTACIÓN PARA DESARROLLADORES
====================================================================

1) Estilos y diseño:
   - Este wizard usa utilidades Tailwind + una capa de componentes propios (prefijo .co-*)
     definida en resources/css/co-wizard.css. Importa esa hoja en app.css:
       @import "co-wizard.css";
   - Usa la variable CSS --color-accent (de Flux) para acentos consistentes.
   - Mantenemos dark-mode con clases dark:* ya presentes en la app.

2) Livewire y UX:
   - Se usa wire:model.change en selects/checkboxes para evitar eventos por tecla.
   - Botones “Siguiente” y “Crear Evaluación” se deshabilitan según validaciones locales.
   - Validaciones definitivas viven en el componente (validateCoachee/validateGuideMethod/
     validateTemplateSelection); aquí solo reflejamos estado para UX.

3) División:
   - Es SIEMPRE obligatoria en el paso 3 (regla de negocio acordada).
   - La división solo clasifica la sesión; NO filtra el catálogo de guías.

4) Accesibilidad:
   - Labels vinculados con for/id donde aplica.
   - Mensajes de error/ayuda con tamaño pequeño y contraste adecuado.
   - Se agregaron wire:key en listados para minimizar rerender difuso.

5) i18n:
   - Se usa __('general.select_division') donde aplica.
   - Si agregas más textos, añádelos a lang/es/*.php.

6) Rendimiento:
   - Evita consultas N+1 en el controlador Livewire (usa with() donde corresponda).
   - La tabla de coachees está paginada en $coacheesPaginated.

==================================================================== --}}

<div class="co-wizard">
  <form aria-labelledby="wizard-title" wire:submit.prevent="save">

    {{-- ======= PASO 1: Selección de coachee ======= --}}
    @if ($currentStep === 1)
      <h2 class="co-step-title" id="wizard-title">
        Paso 1: Selecciona al colaborador (coachee)
      </h2>

      @if ($coacheesPaginated->count())
        <div class="co-table-shell">
          <table class="co-table">
            <thead>
              <tr>
                <th class="font-medium">Colaborador</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($coacheesPaginated as $user)
                <tr class="co-tr {{ $coacheeId === $user->id ? 'co-tr--selected' : '' }}"
                  wire:key="coachee-row-{{ $user->id }}">
                  <td>
                    <div class="flex items-center gap-3">
                      <flux:profile :avatar="$user->profile_photo_url" :chevron="false" :name="$user->name"
                        circle />
                      <div class="co-divider"></div>
                      <div class="min-w-0">
                        <p class="truncate font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="text-right">
                    <flux:button class="min-w-[100px]" variant="{{ $coacheeId === $user->id ? 'filled' : 'primary' }}"
                      wire:click="$set('coacheeId', {{ $user->id }})">
                      {{ $coacheeId === $user->id ? 'Seleccionado' : 'Seleccionar' }}
                    </flux:button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <div class="co-pagination">
            {{ $coacheesPaginated->links() }}
          </div>
        </div>
      @else
        <div class="co-alert" role="status">
          No tienes coachees a tu cargo. Solicita al administrador que asigne colaboradores.
        </div>
      @endif

      {{-- ======= PASO 2: Método de selección ======= --}}
    @elseif ($currentStep === 2)
      <h2 class="co-step-title" id="wizard-title">
        Paso 2: ¿Cómo deseas elegir la(s) guía(s)?
      </h2>

      <flux:radio.group class="grid gap-4 sm:grid-cols-2" wire:model.change="useGroup">
        <flux:label class="co-choice {{ $useGroup === '1' ? 'co-choice--active' : '' }}">
          <flux:radio aria-label="Desde grupo de guías" class="mt-0.5" value="1" />
          <div class="ml-3">
            <h3 class="font-medium text-slate-900 dark:text-white">Desde grupo de guías</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Aplica un grupo predefinido de guías (todas juntas).
            </p>
          </div>
        </flux:label>

        <flux:label class="co-choice {{ $useGroup === '0' ? 'co-choice--active' : '' }}">
          <flux:radio aria-label="Selección personalizada" class="mt-0.5" value="0" />
          <div class="ml-3">
            <h3 class="font-medium text-slate-900 dark:text-white">Selección personalizada</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Selecciona una o más guías de todo el listado disponible.
            </p>
          </div>
        </flux:label>
      </flux:radio.group>

      {{-- ======= PASO 3: División + selección de guías ======= --}}
    @elseif ($currentStep === 3)
      <h2 class="co-step-title" id="wizard-title">
        Paso 3: Selecciona la(s) guía(s) a aplicar y {{ __('general.select_division') }}
      </h2>
      {{-- Ciclo (SIEMPRE obligatorio) --}}
      <div class="mb-6">
        <flux:label for="cycleId">Ciclo</flux:label>

        @if ($cycles->count())
          <flux:select class="co-input mt-1 w-full" id="cycleId" placeholder="-- Selecciona ciclo (Q1–Q4) --"
            wire:model.change="cycleId">
            <flux:select.option value="">-- Selecciona un ciclo --</flux:select.option>
            @foreach ($cycles as $cy)
              <flux:select.option value="{{ $cy->id }}">
                {{ $cy->label_with_range }} @if ($cy->is_open)
                  • Abierto
                @endif
              </flux:select.option>
            @endforeach
          </flux:select>

          @error('cycleId')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
          @enderror
        @else
          <div class="co-alert mt-2">
            No hay ciclos activos. Contacta a TI para configurarlos.
          </div>
        @endif

        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          El ciclo se guarda como etiqueta legible (ej. FY2025-Q1) en la sesión.
        </p>
      </div>
      {{-- División (SIEMPRE obligatoria) --}}
      <div class="mb-6">
        <flux:label for="divisionId">{{ __('general.select_division') }}</flux:label>

        @if ($divisions->count())
          <flux:select :disabled="$divisions->isEmpty()" class="mt-1 w-full" id="divisionId"
            wire:model.change="divisionId">
            <flux:select.option value="">-- Selecciona división --</flux:select.option>
            @foreach ($divisions as $div)
              <flux:select.option value="{{ $div->id }}">{{ $div->name }}</flux:select.option>
            @endforeach
          </flux:select>

          @error('divisionId')
            <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
          @enderror
        @else
          <div class="co-alert mt-2" role="status">
            No hay divisiones configuradas. Contacta a TI para agregarlas.
          </div>
        @endif

        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          La división clasifica la evaluación; el catálogo de guías no se filtra por este campo.
        </p>
      </div>

      {{-- Método: Grupo de guías --}}
      @if ($useGroup === '1')
        <div>
          <flux:label for="guideGroupId">Grupo de Guías</flux:label>

          @if ($guideGroups->count())
            <flux:select :disabled="$guideGroups->isEmpty()" class="co-input mt-1" id="guideGroupId"
              wire:model.change="guideGroupId">
              <option value="">-- Selecciona grupo --</option>
              @foreach ($guideGroups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
              @endforeach
            </flux:select>

            @if ($guideGroupId)
              @php $selectedGroup = $guideGroups->firstWhere('id', $guideGroupId); @endphp

              @if ($selectedGroup && $selectedGroup->templates->count())
                <div class="co-grid-cards mt-6">
                  @foreach ($selectedGroup->templates as $template)
                    <div class="co-guide-card" wire:key="group-template-{{ $template->id }}">
                      <div class="mb-2 flex items-center gap-3">
                        <flux:icon.archive-box class="co-guide-card__icon" />
                        <span class="co-guide-card__title">{{ $template->name }}</span>
                      </div>
                      <p class="co-guide-card__desc">
                        {{ $template->description ?? 'Guía sin descripción' }}
                      </p>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="co-alert mt-6" role="status">
                  El grupo seleccionado no contiene guías asignadas.
                </div>
              @endif
            @endif
          @else
            <div class="co-alert mt-4" role="status">
              No existen grupos de guías configurados.
            </div>
          @endif
        </div>

        {{-- Método: Selección personalizada --}}
      @else
        <div>
          <flux:label>Guías disponibles (selecciona una o más)</flux:label>

          @if ($catalogTemplates->count())
            <div class="co-grid-cards">
              @foreach ($catalogTemplates as $template)
                <label class="co-guide-card group relative flex cursor-pointer items-start gap-3"
                  wire:key="catalog-template-{{ $template->id }}">
                  <flux:checkbox :checked="in_array($template->id, $guideTemplateIds ?? [])" class="mt-1"
                    value="{{ $template->id }}" wire:model.change="guideTemplateIds" />
                  <div>
                    <div class="flex items-center gap-2">
                      <flux:icon.newspaper class="co-guide-card__icon" name="newspaper" />
                      <span class="co-guide-card__title">{{ $template->name }}</span>
                    </div>
                    <p class="co-guide-card__desc">
                      Nivel: {{ $template->level->name ?? 'Sin nivel' }}
                    </p>
                    <p class="co-guide-card__desc">
                      Canal: {{ $template->channel->name ?? 'Sin canal' }}
                    </p>
                  </div>
                </label>
              @endforeach
            </div>

            @if ($showTemplateSelectionError)
              <div class="co-alert mt-6" role="alert">
                No se seleccionaron guías.
              </div>
            @endif
          @else
            <div class="co-alert mt-4" role="status">
              No existen guías disponibles actualmente.
            </div>
          @endif
        </div>
      @endif
    @endif

    {{-- ======= FOOTER / Navegación entre pasos ======= --}}
    @php
      $onStep1 = $currentStep === 1;
      $onStep2 = $currentStep === 2;
      $onStep3 = $currentStep === 3;

      $step1Invalid = $onStep1 && !$coacheeId;
      $step2Invalid = $onStep2 && !in_array($useGroup, ['0', '1'], true);

      // Paso 3: obligatorios
      $cycleInvalid = $onStep3 && empty($cycleId);
      $divisionInvalid = $onStep3 && empty($divisionId);

      // Validaciones según método
      $groupInvalid =
          $onStep3 &&
          $useGroup === '1' &&
          (empty($guideGroupId) || empty(optional($guideGroups->firstWhere('id', $guideGroupId))->templates));

      $customInvalid = $onStep3 && $useGroup === '0' && empty($guideTemplateIds);

      $shouldDisableNext =
          $step1Invalid || $step2Invalid || $cycleInvalid || $divisionInvalid || $groupInvalid || $customInvalid;
      $shouldDisableSubmit = $shouldDisableNext;
    @endphp

    <div class="co-footer">
      @if ($currentStep > 1)
        <flux:button class="co-btn-prev" color="zinc" type="button" variant="primary" wire:click="previousStep">
          ← Anterior
        </flux:button>
      @else
        <div></div>
      @endif

      @if ($currentStep < 3)
        <flux:button :disabled="$shouldDisableNext" type="button" variant="primary" wire:click="nextStep">
          Siguiente →
        </flux:button>
      @elseif ($currentStep === 3)
        <flux:button :disabled="$shouldDisableSubmit" type="submit" variant="primary" wire:loading.attr="disabled">
          Crear Evaluación
        </flux:button>
      @endif
    </div>
  </form>
</div>
