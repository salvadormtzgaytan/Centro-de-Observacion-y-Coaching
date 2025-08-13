<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendUserCredentials extends Notification
{
    use Queueable;

    protected string $email;

    protected string $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tus credenciales de acceso')
            ->greeting('Hola,')
            ->line('Se han creado tus credenciales de acceso para nuestra plataforma.')
            ->line('**Correo electrónico:** '.$this->email)
            ->line('**Contraseña:** '.$this->password)
            ->line('Por favor, inicia sesión y cambia tu contraseña lo antes posible.')
            ->action('Iniciar sesión', url('/login'))
            ->line('Gracias por usar nuestra plataforma.');
    }
}
