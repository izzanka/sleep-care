<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('Jika email anda terdaftar, kami akan mengirimkan link reset password.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Lupa password" description="" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            label="{{ __('Email') }}"
            type="email"
            name="email"
            required
            autofocus
            placeholder="email@example.com"
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Kirim email reset password') }}</flux:button>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-400">
        Atau, kembali ke
        <flux:link href="{{ route('login') }}" wire:navigate>Login</flux:link>
    </div>
</div>
