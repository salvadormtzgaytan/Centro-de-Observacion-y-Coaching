<?php
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Envía un enlace para restablecer la contraseña al correo proporcionado.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Envía el enlace de restablecimiento
        $status = Password::sendResetLink($this->only('email'));

        // Mensaje de confirmación
        session()->flash('status', __($status === Password::RESET_LINK_SENT
            ? 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.'
            : 'Hubo un error al procesar tu solicitud. Por favor inténtalo de nuevo.'
        ));
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Encabezado -->
    <x-auth-header 
        :title="__('Recuperar contraseña')" 
        :description="__('Ingresa tu correo electrónico para recibir un enlace de recuperación')" 
    />

    <!-- Estado de sesión -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Correo electrónico -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autofocus
            placeholder="tu@correo.com"
        />

        <!-- Botón de envío -->
        <flux:button 
            variant="primary" 
            type="submit" 
            class="w-full"
        >
            {{ __('Enviar enlace de recuperación') }}
        </flux:button>
    </form>

    <!-- Enlace alternativo -->
    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('O regresa a') }}
        <flux:link 
            :href="route('login')" 
            class="font-medium text-primary-600 hover:underline"
            wire:navigate
        >
            {{ __('iniciar sesión') }}
        </flux:link>
    </div>
</div>