<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Encabezado actualizado -->
    <x-auth-header 
        :title="__('Iniciar sesión')" 
        :description="__('Ingresa tu correo electrónico y contraseña para acceder')" 
    />

    <!-- Estado de sesión -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Correo electrónico -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="tu@correo.com"
        />

        <!-- Contraseña -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link 
                    class="absolute end-0 top-0 text-sm text-primary-600 hover:underline"
                    :href="route('password.request')"
                >
                    {{ __('¿Olvidaste tu contraseña?') }}
                </flux:link>
            @endif
        </div>

        <!-- Recordar sesión -->
        <flux:checkbox 
            wire:model="remember" 
            :label="__('Recordar sesión')" 
        />

        <!-- Botón de acceso -->
        <flux:button 
            variant="primary" 
            type="submit" 
            class="w-full"
        >
            {{ __('Ingresar') }}
        </flux:button>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('¿No tienes una cuenta?') }}
            <flux:link 
                :href="route('register')" 
                class="font-medium text-primary-600 hover:underline"
            >
                {{ __('Regístrate') }}
            </flux:link>
        </div>
    @endif
</div>
