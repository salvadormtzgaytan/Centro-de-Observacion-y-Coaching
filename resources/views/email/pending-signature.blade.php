{{-- resources/views/email/pending-signature.blade.php --}}

@component('mail::message')
# {{ __('Tienes una evaluación pendiente de firma') }}

Hola {{ $participant->name }},

Tu evaluación está lista para revisión y firma.

@component('mail::panel')
**ID:** {{ $session->id }}  
**Fecha:** {{ optional($session->date)->format('d/m/Y') ?? '—' }}  
**Ciclo:** {{ $session->cycle ?? '—' }}  
**Puntaje:** {{ number_format((float) $session->total_score, 2) }}
@endcomponent

@component('mail::button', ['url' => $url])
Revisar y firmar ahora
@endcomponent

Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
{{ $url }}

Gracias,<br>
{{ config('app.name') }}
@endcomponent

