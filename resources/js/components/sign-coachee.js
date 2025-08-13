// resources/js/sign-coachee.js
import SignaturePad from 'signature_pad';

(function init() {
  const canvas = document.getElementById('signature-canvas');
  const form = document.getElementById('sign-form');
  const input = document.getElementById('signature-input');
  const btnClear = document.getElementById('btn-clear');
  const btnSave = document.getElementById('btn-save');
  const previewWrap = document.getElementById('sig-preview-wrap');
  const previewImg = document.getElementById('sig-preview');

  if (!canvas || !form || !input || !btnClear || !btnSave) return;

  const ctx = canvas.getContext('2d');
  const dpr = Math.max(window.devicePixelRatio || 1, 1);

  // Calcula penColor según tema
  const isDark = () => document.documentElement.classList.contains('dark');
  const penColor = () => (isDark() ? '#e5e7eb' : '#0f172a'); // slate-200 / slate-900

  let sigPad;

  function resizeCanvas(preserve = true) {
    // guarda lo actual para restaurar luego
    const dataUrl = preserve && sigPad && !sigPad.isEmpty() ? sigPad.toDataURL('image/png') : null;

    // Limpia cualquier transform previo
    ctx.setTransform(1, 0, 0, 1, 0, 0);

    const cssW = canvas.clientWidth;
    const cssH = canvas.clientHeight;

    canvas.width = Math.floor(cssW * dpr);
    canvas.height = Math.floor(cssH * dpr);

    // Escala coordenadas al tamaño CSS
    ctx.scale(dpr, dpr);

    // (Re)inicializa SignaturePad sobre el canvas actual
    if (sigPad) sigPad.off(); // quita listeners previos
    sigPad = new SignaturePad(canvas, {
      minWidth: 1.5,
      maxWidth: 2.5,
      throttle: 8, // suaviza en móviles
      penColor: penColor(),
    });

    // Habilita/Deshabilita botón según contenido
    const toggleSave = () => {
      if (sigPad.isEmpty()) {
        btnSave.setAttribute('disabled', 'disabled');
      } else {
        btnSave.removeAttribute('disabled');
      }
    };

    // Eventos de cambio
    sigPad.addEventListener('beginStroke', () => {
      // nada
    });
    sigPad.addEventListener('endStroke', toggleSave);

    // Restaurar firma si existía
    if (dataUrl) {
      try {
        sigPad.fromDataURL(dataUrl);
      } catch {
        // si falla (por tamaño diferente), lo ignoramos
      }
    }

    // Ajusta estado inicial
    toggleSave();
  }

  // Listeners UI
  btnClear.addEventListener('click', () => {
    sigPad.clear();
    btnSave.setAttribute('disabled', 'disabled');
    input.value = '';
    if (previewWrap) {
      previewWrap.classList.add('hidden');
      if (previewImg) previewImg.removeAttribute('src');
    }
  });

  btnSave.addEventListener('click', () => {
    if (sigPad.isEmpty()) {
      // pequeño feedback visual sin libs
      btnSave.animate([{ opacity: 0.5 }, { opacity: 1 }], { duration: 150, iterations: 2 });
      return;
    }

    // Exporta PNG transparente (nítido por DPR)
    const dataUrl = sigPad.toDataURL('image/png');
    input.value = dataUrl;

    if (previewImg) {
      previewImg.src = dataUrl;
      previewWrap.classList.remove('hidden');
    }

    // Evita doble envío
    btnSave.setAttribute('disabled', 'disabled');
    btnSave.textContent = 'Guardando...';

    form.submit();
  });

  // Inicializa y maneja resize / tema
  resizeCanvas(false);
  let rTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(rTimer);
    rTimer = setTimeout(() => resizeCanvas(true), 200);
  });

  // Si cambia el tema, reconfigura color (y preserva firma)
  const mqDark = window.matchMedia?.('(prefers-color-scheme: dark)');
  if (mqDark?.addEventListener) {
    mqDark.addEventListener('change', () => {
      // sólo re-instancia para actualizar penColor
      resizeCanvas(true);
    });
  }
})();
