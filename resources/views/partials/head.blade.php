<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" sizes="57x57" href="/img/logos/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/img/logos/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/img/logos/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/img/logos/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/img/logos/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/img/logos/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/img/logos/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/img/logos/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/img/logos/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="/img/logos/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/img/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/img/logos/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img/logos/favicon-16x16.png">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/img/logos/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">

{{-- Carga de Tailwind + tu JS bundle (Alpine ya viene con Flux) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Tema Flux UI (incluye solo CSS de Flux) --}}
@fluxAppearance

{{-- Estilos que Livewire necesita (p. ej. para validaciones remotas) --}}
@livewireStyles
<!-- Otros estilos -->
@stack('styles')
