<?php

use App\Enum\UserRole;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                if ($user->role != UserRole::DOCTOR->value){
                    return Password::INVALID_USER;
                }

                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login');
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Reset password" description=""/>

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            id="email"
            label="{{ __('Email') }}"
            type="email"
            name="email"
            required
            autocomplete="email"
        />

        <flux:input
            wire:model="password"
            id="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="Password"
        />

        <flux:input
            wire:model="password_confirmation"
            id="password_confirmation"
            label="{{ __('Konfirmasi password') }}"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            placeholder="Confirm password"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Reset password') }}
            </flux:button>
        </div>
    </form>
</div>
