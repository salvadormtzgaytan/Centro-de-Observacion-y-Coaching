// resources/js/quill-editor.js
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const quillConfigs = {
  simple: { toolbar: [['bold', 'italic'], ['link'], ['clean']] },
  standard: {
    toolbar: [
      ['bold', 'italic', 'underline'],
      ['link', 'blockquote'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['clean'],
    ],
  },
  full: {
    toolbar: [
      [{ header: [1, 2, 3, false] }],
      ['bold', 'italic', 'underline'],
      ['link', 'blockquote'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['image'],
      ['clean'],
    ],
  },
};

document.addEventListener('alpine:init', () => {
  Alpine.data('quillEditor', () => ({
    editor: null,
    hidden: null,

    initEditor(id, opts = {}) {
      this.$nextTick(() => {
        const elementId = `quill-${id}`;
        const target = document.getElementById(elementId);
        if (!target || target.querySelector('.ql-toolbar')) return;

        const config = opts.config || 'standard';
        const readOnly = !!opts.readOnly;
        const initialValue = opts.initialValue || '';
        const placeholder = target.dataset.placeholder || 'Escribe aquí...';

        this.editor = new Quill(`#${elementId}`, {
          theme: 'snow',
          readOnly,
          placeholder,
          modules: { toolbar: quillConfigs[config]?.toolbar || quillConfigs.standard.toolbar },
        });

        // set initial
        if (initialValue) {
          this.editor.root.innerHTML = initialValue;
        }

        // input hidden vinculado con wire:model.defer
        this.hidden = document.getElementById(`${elementId}-hidden`);
        const syncToHidden = () => {
          if (!this.hidden) return;
          this.hidden.value = this.editor.root.innerHTML;
          // Notifica a Livewire que cambió el input, pero sin request (por ser .defer)
          this.hidden.dispatchEvent(new Event('input', { bubbles: true }));
        };

        // Actualiza el hidden en cada cambio, pero NO llama a $wire.set
        this.editor.on('text-change', syncToHidden);

        // Si quieres aún menos ruido en DOM: sólo en blur
        // this.editor.root.addEventListener('blur', syncToHidden);
      });
    },
  }));
});
