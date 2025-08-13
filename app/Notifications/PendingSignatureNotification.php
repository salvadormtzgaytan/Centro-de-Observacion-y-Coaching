<?php

namespace App\Notifications;

use App\Models\EvaluationSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class PendingSignatureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * La sesión de evaluación pendiente de firma.
     *
     * @var EvaluationSession
     */
    public EvaluationSession $session;

    /**
     * URL donde el participante debe firmar.
     *
     * @var string
     */
    public string $url;

    /**
     * Crea una nueva notificación.
     *
     * @param  EvaluationSession  $session
     * @param  string             $url
     * @return void
     */
    public function __construct(EvaluationSession $session, string $url)
    {
        $this->session = $session;
        $this->url     = $url;
        // Log para debugging
        // Log::info('PendingSignatureNotification creada', [
        //     'session_id' => $session->id,
        //     'url' => $url
        // ]);
    }

    /**
     * Canales de entrega de la notificación.
     *
     * @param  mixed  $notifiable
     * @return array<int,string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }


    /**
     * Representación por correo de la notificación.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Log para debugging
        // Log::info('Enviando notificación por correo', [
        //     'to' => $notifiable->email,
        //     'session_id' => $this->session->id
        // ]);
        return (new MailMessage)
            ->subject('🔔 Evaluación lista para tu firma')
            ->markdown('email.pending-signature', [
                'participant' => $notifiable,
                'session'     => $this->session,
                'url'         => $this->url,
            ]);
    }

    /**
     * Representación en array (para, por ejemplo, canal "database").
     *
     * @param  mixed  $notifiable
     * @return array<string,mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'url'        => $this->url,
            'message'    => "Tienes una evaluación (ID {$this->session->id}) pendiente de firma.",
        ];
    }
}
