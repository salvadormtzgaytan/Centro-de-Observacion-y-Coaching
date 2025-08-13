import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/css/print.css',
        'resources/js/app.js',
        'resources/js/components/sign-coachee.js',
        'resources/js/components/radar.js',
        'resources/js/components/evaluation-pdf-export.js',
      ],
      refresh: true,
    }),
    tailwindcss(),
  ],
});
