@php use App\Models\TemplateItem as TI; @endphp

<div>
    <div class="co-page">

        {{-- Encabezado --}}
        <div class="mb-6">
            <h1 class="co-title">Evaluando a:</h1>

            <div class="mb-3 flex items-center gap-3">
                <flux:profile :avatar="$session->participant->profile_photo_url" :chevron="false" circle />
                <div class="co-divider"></div>

                <div class="min-w-0">
                    <p class="truncate font-medium text-slate-900 dark:text-slate-50">
                        {{ $session->participant->name }}
                    </p>
                    <p class="truncate text-sm text-slate-600 dark:text-slate-400">
                        {{ $session->participant->email }}
                    </p>
                </div>

                <div class="ml-auto flex items-center gap-3">
                    {{-- Resumen de puntajes persistidos --}}
                    <div class="hidden items-center gap-4 text-sm md:flex">
                        <div class="text-slate-500 dark:text-slate-400">
                            <span class="font-medium text-slate-700 dark:text-slate-200">Prom. real: </span>
                            @php $ov = $session->overall_avg; @endphp
                            <span>{{ $ov !== null ? number_format($ov * 100, 2) . '%' : '—' }}</span>
                        </div>
                        <div class="text-slate-500 dark:text-slate-400">
                            <span class="font-medium text-slate-700 dark:text-slate-200">Prom. actual: </span>
                            @php $an = $session->answered_avg; @endphp
                            <span>{{ $an !== null ? number_format($an * 100, 2) . '%' : '—' }}</span>
                        </div>
                        <div class="text-slate-500 dark:text-slate-400">
                            <span class="font-medium text-slate-700 dark:text-slate-200">Total / Máx: </span>
                            <span>{{ number_format((float) ($session->total_score ?? 0), 2) }} /
                                {{ number_format((float) ($session->max_score ?? 0), 2) }}</span>
                        </div>
                    </div>

                    <flux:badge color="{{ $session->status_pill_color }}" variant="pill">
                        {{ $session->status_name }}
                    </flux:badge>
                </div>
            </div>

            <p class="co-subtitle">Selecciona la guía a evaluar y registra las respuestas.</p>
        </div>

        @if ($this->readOnly)
            <div class="co-alert-ro">
                Esta sesión ya está firmada o completada. Los campos se muestran en solo lectura.
            </div>
        @endif

        {{-- Tabs --}}
        <div class="mb-6">
            <nav class="co-tabs">
                @foreach ($session->guideResponses as $i => $gr)
                    @php $active = $currentTab === $i; @endphp
                    <button class="co-tab {{ $active ? 'co-tab--active' : '' }}" type="button"
                        wire:click="setTab({{ $i }})" wire:key="tab-{{ $gr->id }}">
                        <span class="flex items-center gap-1">
                            <span class="max-w-[16rem] truncate">{{ $gr->guideTemplate->name }}</span>
                            @if ($this->isComplete($gr->id))
                                <flux:icon.check class="text-emerald-500" variant="mini" />
                            @endif
                        </span>
                    </button>
                @endforeach
            </nav>

            {{-- Panel --}}
            <div class="co-panel">
                @php $guideResponse = $session->guideResponses[$currentTab] ?? null; @endphp

                @if ($guideResponse)
                    <form aria-live="polite" class="{{ $this->readOnly ? 'co-readonly' : '' }} space-y-8"
                        wire:submit.prevent="save">
                        @foreach ($guideResponse->guideTemplate->sections as $section)
                            <div class="co-section">
                                <h3 class="co-section-h3">{{ $section->title }}</h3>

                                <div class="space-y-6">
                                    @foreach ($section->items as $item)
                                        @php $vertical = in_array($item->type, [TI::TYPE_TEXT], true); @endphp

                                        <div class="{{ $vertical ? 'co-row-v' : 'co-row' }}"
                                            wire:key="item-{{ $guideResponse->id }}-{{ $item->id }}">
                                            <div class="flex-1">
                                                <label class="co-label">{!! $item->question !!}</label>
                                                @if ($item->help_text)
                                                    <div class="co-help">{!! $item->help_text !!}</div>
                                                @endif
                                            </div>

                                            <div class="co-field">
                                                {{-- INPUTS --}}
                                                {{-- Texto --}}
                                                @if (in_array($item->type, [TI::TYPE_TEXT], true))
                                                    <div class="co-input rounded-md border">
                                                        <x-quill-livewire :initial-value="$answers[$guideResponse->id][$item->id]['value'] ??
                                                            ''" :read-only="$this->readOnly"
                                                            config="standard" height="200"
                                                            id="{{ $guideResponse->id }}-{{ $item->id }}"
                                                            placeholder="Escribe tu respuesta…"
                                                            wire-model="answers.{{ $guideResponse->id }}.{{ $item->id }}.value" />
                                                    </div>
                                                    {{-- Select / Scale --}}
                                                @elseif (in_array($item->type, [TI::TYPE_SELECT, TI::TYPE_SCALE], true))
                                                    <flux:select :disabled="$this->readOnly" class="co-input"
                                                        wire:model.defer="answers.{{ $guideResponse->id }}.{{ $item->id }}.option">
                                                        <flux:select.option value="">Selecciona…
                                                        </flux:select.option>
                                                        @foreach ($this->scales as $scale)
                                                            <flux:select.option value="{{ $scale->value }}">
                                                                {{ $scale->label }}
                                                                ({{ $scale->value }})
                                                            </flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                    {{-- Radio --}}
                                                @elseif ($item->type === TI::TYPE_RADIO)
                                                    <div class="co-input">
                                                        <flux:radio.group :disabled="$this->readOnly"
                                                            class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                                                            wire:model.defer="answers.{{ $guideResponse->id }}.{{ $item->id }}.option">
                                                            @foreach ($this->scales as $scale)
                                                                <flux:radio
                                                                    id="rd-{{ $guideResponse->id }}-{{ $item->id }}-{{ $scale->id }}"
                                                                    label="{{ $scale->label }} ({{ $scale->value }})"
                                                                    value="{{ $scale->value }}" />
                                                            @endforeach
                                                        </flux:radio.group>
                                                    </div>
                                                    {{-- Default --}}
                                                @else
                                                    <flux:input :disabled="$this->readOnly" class="co-input"
                                                        placeholder="Respuesta" type="text"
                                                        wire:model.defer="answers.{{ $guideResponse->id }}.{{ $item->id }}" />
                                                @endif
                                                {{-- /INPUTS --}}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-6 flex flex-wrap justify-end gap-2">
                            <flux:button :disabled="$this->readOnly" type="submit" variant="primary"
                                wire:loading.attr="disabled" wire:target="save">
                                Guardar todas las respuestas
                            </flux:button>
                        </div>

                        {{-- Overlay de guardado --}}
                        <div aria-label="Guardando respuestas" class="co-overlay" role="status" wire:loading.flex
                            wire:target="save">
                            <div class="co-overlay-box">Guardando…</div>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-slate-600 dark:text-slate-400">No hay contenido para este tab.</p>
                @endif
            </div>
        </div>

        {{-- Botón firmar --}}
        @if ($this->allComplete() && !$this->readOnly)
            <div class="mt-8 flex justify-center">
                <flux:button type="button" variant="primary" wire:click="goToSignature" wire:loading.attr="disabled"
                    wire:target="goToSignature">
                    Firmar evaluación
                </flux:button>
            </div>
        @endif

        {{-- Modal de firma --}}
        @if ($showSignatureModal)
            <div class="co-modal-wrap">
                <div class="co-modal">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900 dark:text-slate-50">Firma tu evaluación</h2>
                    <flux:text class="mt-2">Una vez firmada la evaluación, no se puede modificar.</flux:text>
                    <div class="co-section">
                        <canvas class="block h-56 w-full" id="signature-pad" wire:ignore></canvas>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <flux:button color="zinc" type="button" variant="primary" wire:click="closeSignatureModal">
                            Cancelar
                        </flux:button>
                        <flux:button color="zinc" onclick="clearSignature()" type="button" variant="primary">
                            Limpiar
                        </flux:button>
                        <flux:button loading="saveSignature" onclick="saveSignature()" type="button" variant="primary">
                            Guardar Firma
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
