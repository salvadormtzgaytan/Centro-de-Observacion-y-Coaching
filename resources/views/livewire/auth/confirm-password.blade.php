<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $password = '';

    /**
     * Confirma la contraseña del usuario actual.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('La contraseña ingresada es incorrecta.'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Encabezado -->
    <x-auth-header
        :title="__('Confirmar contraseña')"
        :description="__('Esta es un área segura de la aplicación. Por favor confirma tu contraseña para continuar.')"
    />

    <!-- Estado de sesión -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <!-- Contraseña -->
        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="current-password"
            placeholder="Ingresa tu contraseña"
            viewable
        />

        <!-- Botón de confirmación -->
        <flux:button 
            variant="primary" 
            type="submit" 
            class="w-full"
        >
            {{ __('Confirmar') }}
        </flux:button>
    </form>
</div>