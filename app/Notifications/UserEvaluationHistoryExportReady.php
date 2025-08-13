<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación: la exportación del historial de evaluaciones del usuario está lista para descargar.
 */
class UserEvaluationHistoryExportReady extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $downloadUrl;

    public function __construct(string $downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu historial de evaluaciones está listo para descargar')
            ->line('El archivo Excel con tu historial de evaluaciones ya está disponible.')
            ->action('Descargar historial', $this->downloadUrl)
            ->line('Gracias por usar el Centro de Observación y Coaching.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Tu historial de evaluaciones está listo para descargar.',
            'url'     => $this->downloadUrl,
        ];
    }
}
