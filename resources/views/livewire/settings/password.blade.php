<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('status', ['message' => 'Password berhasil diubah.', 'success' => true]);
    }
}; ?>

<section class="w-full">
    @include('partials.main-heading', ['title' => 'Password'])

    <x-settings.layout heading="{{ __('Ubah password') }}"
                       subheading="{{ __('Pastikan akun anda menggunakan password yang panjang dan acak agar tetap aman') }}">
        <form wire:submit="updatePassword" class="space-y-6">
            <flux:input
                    wire:model="current_password"
                    id="update_password_current_password"
                    label="{{ __('Password saat ini') }}"
                    type="password"
                    name="current_password"
                    required
                    autocomplete="current-password"
                    viewable
            />
            <flux:input
                    wire:model="password"
                    id="update_password_password"
                    label="{{ __('Password baru') }}"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    viewable
            />
            <flux:input
                    wire:model="password_confirmation"
                    id="update_password_password_confirmation"
                    label="{{ __('Konfirmasi password') }}"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    viewable
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Simpan') }}</flux:button>
                </div>

                {{--                <x-action-message class="me-3 text-green-600" on="password-updated">--}}
                {{--                    {{ __('Tersimpan.') }}--}}
                {{--                </x-action-message>--}}
            </div>
        </form>
    </x-settings.layout>
</section>
