// resources/js/signature.js
import SignaturePad from 'signature_pad';
import Swal from 'sweetalert2';

// Evita registrar listeners múltiples si Vite hace HMR
if (!window.__coaching_sigpad_init) {
  window.__coaching_sigpad_init = true;

  let signaturePad = null;
  let resizeHandler = null;
  let keydownHandler = null;

  // --- Toast helper (usado por eventos Livewire) ---
  const toast = ({ type, message, position, timer, timerProgressBar }) => {
    Swal.close();
    Swal.fire({
      toast: true,
      position: position || 'top-end',
      icon: type || 'info',
      title: message || '',
      showConfirmButton: false,
      timer: timer ?? 3000,
      timerProgressBar: timerProgressBar ?? true,
    });
  };

  // --- Limpieza completa de recursos del SignaturePad ---
  function cleanupSignaturePad() {
    if (signaturePad) {
      // signature_pad no expone off() en todas las versiones; protege el call
      try {
        signaturePad.off();
      } catch {}
      signaturePad = null;
    }
    if (resizeHandler) {
      window.removeEventListener('resize', resizeHandler);
      resizeHandler = null;
    }
    if (keydownHandler) {
      window.removeEventListener('keydown', keydownHandler);
      keydownHandler = null;
    }
    delete window.clearSignature;
    delete window.saveSignature;
  }

  // --- Inicializa el canvas y el SignaturePad ---
  function setupSignaturePad() {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) {
      console.error('[signature] Canvas #signature-pad no encontrado');
      return;
    }

    // Asegura canvas usable (táctil + cursor)
    canvas.style.touchAction = 'none';
    canvas.style.cursor = 'crosshair';

    // Limpia instancia anterior si la hubiera
    cleanupSignaturePad();

    // Función de resize que conserva el trazo
    resizeHandler = () => {
      if (!signaturePad) return;
      const ctx = canvas.getContext('2d');
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = canvas.getBoundingClientRect();

      // Guarda trazos existentes
      const data = signaturePad.toData();

      canvas.width = Math.max(1, Math.floor(rect.width * ratio));
      canvas.height = Math.max(1, Math.floor(rect.height * ratio));
      ctx.scale(ratio, ratio);
      canvas.style.width = rect.width + 'px';
      canvas.style.height = rect.height + 'px';

      // Restaura trazos
      signaturePad.clear();
      try {
        signaturePad.fromData(data);
      } catch {}
    };

    // Dimensiones iniciales
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    // Calcula penColor según tema
    const isDark = () => document.documentElement.classList.contains('dark');
    const penColor = () => (isDark() ? '#e5e7eb' : '#0f172a'); // slate-200 / slate-900
    // Crea la instancia
    signaturePad = new SignaturePad(canvas, {
      penColor: penColor(),
      minWidth: 1.5,
      maxWidth: 3,
      throttle: 8,
      minDistance: 5,
    });

    // Resize responsivo
    resizeHandler();
    window.addEventListener('resize', resizeHandler);

    // Cerrar con ESC (ergonomía)
    keydownHandler = (e) => {
      if (e.key === 'Escape') {
        // Tu componente debería emitir 'signatureModalClosed' al cerrar
        Livewire.dispatch('signatureModalClosed');
      }
    };
    window.addEventListener('keydown', keydownHandler);

    // APIs globales para botones del modal
    window.clearSignature = () => signaturePad && signaturePad.clear();
    // Si cambia el tema, reconfigura color (y preserva firma)
    const mqDark = window.matchMedia?.('(prefers-color-scheme: dark)');
    if (mqDark?.addEventListener) {
      mqDark.addEventListener('change', () => {
        // sólo re-instancia para actualizar penColor
        resizeHandler();
      });
    }
    window.saveSignature = () => {
      if (!signaturePad) return;
      if (signaturePad.isEmpty()) {
        return Swal.fire({
          icon: 'error',
          title: 'Firma vacía',
          text: 'Por favor, firma antes de guardar.',
        });
      }
      const dataUrl = signaturePad.toDataURL('image/png');
      Livewire.dispatch('saveSignature', { signatureDataUrl: dataUrl });
    };
  }

  // --- Wire-up de eventos Livewire ---
  Livewire.on('toast', toast);

  Livewire.on('signatureModalOpen', () => {
    // espera al DOM del modal
    setTimeout(setupSignaturePad, 100);
  });

  Livewire.on('signatureModalClosed', () => {
    cleanupSignaturePad();
    Swal.close();
  });

  // Si el canvas desaparece del DOM (por diffing de Livewire), limpiamos
  Livewire.hook('element.removed', (el) => {
    if (el && el.id === 'signature-pad') cleanupSignaturePad();
  });
}
