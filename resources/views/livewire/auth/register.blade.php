<?php

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $gender = '';
    public int $age;


    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'gender' => ['required', 'string', 'max:10'],
            'age' => ['required', 'int', 'min:1'],
        ]);

//        $himpsiService = New \App\Service\HimpsiService();
//        $result = $himpsiService->search($this->name, $this->email);
//
//        if ($result === false) {
//            throw ValidationException::withMessages([
//                'email' => __('Proses verifikasi akun HIMPSI gagal, Silahkan coba ulangi lagi nanti.'),
//            ]);
//        }
//
//        if (empty($result)) {
//            throw ValidationException::withMessages([
//                'email' => __('Akun HIMPSI tidak ditemukan'),
//            ]);
//        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 2;

        $user = User::create($validated);

//        Doctor::create([
//            'user_id' => $user->id,
//            'registered_year' => $result['registered_year'],
//            'name_title' => $result['name_title'],
//            'phone' => $result['phone'],
//        ]);

//        event(new Registered(($user)));

        $admin = User::find(1);
        $admin->notify(new \App\Notifications\RegisteredUser($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Register" description=""/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">

            <!-- Name -->
            <flux:input
                wire:model="name"
                id="name"
                label="{{ __('Nama') }}"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama lengkap"
            />
            <!-- Email Address -->
            <flux:input
                wire:model="email"
                id="email"
                label="{{ __('Email') }}"
                type="email"
                name="email"
                required
                autocomplete="email"
                placeholder="Email@example.com"
            />

        <flux:description>Pastikan nama dan email anda sudah terdaftar di
            <flux:link href="https://himpsi.or.id/" target="_blank">HIMPSI</flux:link>
        </flux:description>

        <!-- Password -->
        <flux:input
            wire:model="password"
            id="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="Password"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            id="password_confirmation"
            label="{{ __('Konfirmasi password') }}"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            placeholder="Konfirmasi password"
            viewable
        />

        <flux:input type="number" label="Usia" name="age" wire:model="age" placeholder="Usia" min="1"></flux:input>

        <flux:select wire:model="gender" placeholder="Pilih gender..." label="Gender">
            <flux:select.option>Pria</flux:select.option>
            <flux:select.option>Perempuan</flux:select.option>
        </flux:select>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Register') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Sudah mempunyai akun?
        <flux:link href="{{ route('login') }}" wire:navigate>Login</flux:link>
    </div>
</div>
