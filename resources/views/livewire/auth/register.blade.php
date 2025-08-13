<?php
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Procesa la solicitud de registro.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Encabezado -->
    <x-auth-header 
        :title="__('Crear una cuenta')" 
        :description="__('Ingresa tus datos para registrarte')" 
    />

    <!-- Estado de sesión -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Nombre completo -->
        <flux:input
            wire:model="name"
            :label="__('Nombre completo')"
            type="text"
            required
            autofocus
            autocomplete="name"
            placeholder="Ej: Juan Pérez"
        />

        <!-- Correo electrónico -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autocomplete="email"
            placeholder="tu@correo.com"
        />

        <!-- Contraseña -->
        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="new-password"
            placeholder="••••••••"
            viewable
        />

        <!-- Confirmar Contraseña -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contraseña')"
            type="password"
            required
            autocomplete="new-password"
            placeholder="••••••••"
            viewable
        />

        <!-- Botón de registro -->
        <flux:button 
            type="submit" 
            variant="primary" 
            class="w-full"
        >
            {{ __('Registrarse') }}
        </flux:button>
    </form>

    <!-- Enlace a login -->
    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('¿Ya tienes una cuenta?') }}
        <flux:link 
            :href="route('login')" 
            class="font-medium text-primary-600 hover:underline"
            wire:navigate
        >
            {{ __('Iniciar sesión') }}
        </flux:link>
    </div>
</div>