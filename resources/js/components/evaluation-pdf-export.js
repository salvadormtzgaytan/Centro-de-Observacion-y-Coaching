// evaluation-pdf-export.js
import Swal from 'sweetalert2';
document.addEventListener('alpine:init', () => {
  const generatePdfBtn = document.getElementById('generateChartsPdf');
  const SELECTOR_CHART = 'canvas.evaluation-chart'; // Asegúrate que coincida con tus elementos

  /**
   * Función para esperar a que las gráficas se rendericen completamente
   */
  function waitForChartsToLoad() {
    return new Promise((resolve) => {
      setTimeout(() => {
        const canvases = document.querySelectorAll(SELECTOR_CHART);
        let allLoaded = true;

        canvases.forEach((canvas) => {
          try {
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const hasContent = imageData.data.some((pixel) => pixel !== 0);
            if (!hasContent) allLoaded = false;
          } catch (e) {
            allLoaded = false;
          }
        });

        if (allLoaded || canvases.length === 0) {
          resolve();
        } else {
          setTimeout(resolve, 1000);
        }
      }, 1500);
    });
  }

  /**
   * Función para capturar todas las gráficas
   */
  async function captureAllCharts() {
    await waitForChartsToLoad();

    const charts = [];
    const canvases = document.querySelectorAll(SELECTOR_CHART);

    canvases.forEach((canvas, index) => {
      try {
        const chartName = `Chart_${index + 1}`;
        const imageData = canvas.toDataURL('image/png');

        charts.push({
          namechart: chartName,
          imagechart: imageData,
        });
      } catch (error) {
        console.error(`Error capturando gráfica ${index}:`, error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `Error capturando gráfica ${index}: ${error.message}`,
        });
      }
    });

    return charts;
  }

  /**
   * Event listener para generar PDF
   */
  generatePdfBtn?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.textContent = 'Generando PDF...';

    try {
      Swal.fire({
        title: 'Procesando',
        text: 'Capturando gráficas...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      const charts = await captureAllCharts();

      if (charts.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Advertencia',
          text: 'No se encontraron gráficas para incluir en el PDF',
        });
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) {
        throw new Error('No se encontró el token CSRF');
      }

      const response = await fetch(btn.dataset.pdfRoute, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          session_id: btn.dataset.sessionid,
          charts: charts,
        }),
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || 'Error al generar el PDF');
      }

      if (result.success) {
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: `PDF generado exitosamente con ${charts.length} gráfica(s)`,
        });

        if (result.download_url) {
          window.open(result.download_url, '_blank');
        } else if (result.pdf_path) {
          const a = document.createElement('a');
          a.href = result.pdf_path;
          a.download = result.filename || 'evaluacion_graficas.pdf';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        }
      } else {
        throw new Error(result.message || 'Error al generar el PDF');
      }
    } catch (error) {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message || 'Ocurrió un error al generar el PDF',
      });
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
});
