import Chart from 'chart.js/auto';

const charts = new Map();

function palette() {
  const s = getComputedStyle(document.documentElement);
  return {
    stroke: s.getPropertyValue('--radar-stroke').trim() || '#0f172a',
    fill: s.getPropertyValue('--radar-fill').trim() || 'rgba(99,102,241,.2)',
    grid: s.getPropertyValue('--radar-grid').trim() || 'rgba(15,23,42,.15)',
    point: s.getPropertyValue('--radar-point').trim() || '#6366f1',
  };
}

function initRadar(canvas) {
  if (!canvas || charts.has(canvas.id)) return;

  const cfg = JSON.parse(canvas.dataset.config || '{}');
  const ctx = canvas.getContext('2d');
  const colors = palette();

  const chart = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: cfg.labels || [],
      datasets: (cfg.datasets || []).map((ds) => ({
        label: ds.label,
        data: ds.data,
        fill: true,
        borderColor: colors.point,
        backgroundColor: colors.fill,
        pointBackgroundColor: colors.point,
        pointBorderColor: colors.stroke,
        pointRadius: 3,
        borderWidth: 2,
      })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: { enabled: true },
      },
      scales: {
        r: {
          min: 0,
          max: 100,
          angleLines: { color: colors.grid },
          grid: { color: colors.grid },
          suggestedMin: 0,
          ticks: { backdropColor: 'transparent' },
          pointLabels: { font: { size: 11 } },
        },
      },
    },
  });

  charts.set(canvas.id, chart);
}

function destroyRadar(canvas) {
  const c = charts.get(canvas.id);
  if (c) {
    c.destroy();
    charts.delete(canvas.id);
  }
}

function initAll() {
  document.querySelectorAll('canvas[data-chart="radar"]').forEach(initRadar);
}

// Re-init al cambiar tema (Tailwind dark), o si hay navigation swap
window.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('livewire:navigated', () => {
  // recargar/configurar después de transiciones Livewire si aplica
  initAll();
});

// Si quieres soportar cambio de tema dinámico, puedes escuchar una señal y reiniciar:
window.addEventListener('themechange', () => {
  document.querySelectorAll('canvas[data-chart="radar"]').forEach((c) => {
    destroyRadar(c);
    initRadar(c);
  });
});
