{{-- resources/views/emails/pending-signature-text.blade.php --}}

Firma pendiente de evaluación

Hola {{ $participant->name }},

Tu sesión de evaluación realizada el {{ $session->date->format('d/m/Y') }} por {{ $session->evaluator->name }} está lista para tu firma.

Accede al siguiente enlace para firmar:
{{ $url }}

@if($session->comments)
Comentarios adicionales:
{{ $session->comments }}
@endif

Gracias por tu tiempo,
{{ config('app.name') }}
