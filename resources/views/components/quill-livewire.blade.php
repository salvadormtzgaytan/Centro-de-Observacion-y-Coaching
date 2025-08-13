{{-- resources/views/components/quill-livewire.blade.php --}}
@props([
  'id',
  'wireModel',          // p.ej. "answers.12.99.value"
  'initialValue' => '',
  'config' => 'standard',
  'height' => 200,
  'readOnly' => false,
  'placeholder' => 'Escribe tu respuesta…',
])

<div
  x-data="quillEditor()"
  x-init="initEditor('{{ $id }}', {
      initialValue: @js($initialValue),
      config: '{{ $config }}',
      readOnly: @js($readOnly),
      placeholder: @js($placeholder),
  })"
  class="relative"
>
  {{-- ESTE es el valor que Livewire leerá sólo al enviar (defer) --}}
  <input type="hidden" id="quill-{{ $id }}-hidden" wire:model.defer="{{ $wireModel }}">

  {{-- El contenedor del editor no debe ser tocado por Livewire --}}
  <div id="quill-{{ $id }}"
       wire:ignore
       class="quill-container"
       style="height: {{ $height }}px;"
       data-placeholder="{{ $placeholder }}"></div>
</div>
