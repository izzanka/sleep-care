<?php

use App\Enum\UserGender;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $gender = '';
    public ?string $phone = '';
    public ?string $name_title = '';
    public int $age;
    public int $registered_year;
    public $avatar;
    public $avatar_url;


    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Auth::user()->load('doctor');

        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->gender = Auth::user()->gender->value;
        $this->age = Auth::user()->age;
        $this->avatar_url = Auth::user()->avatar;
        $this->phone = Auth::user()->doctor->phone;
        $this->registered_year = Auth::user()->doctor->registered_year;
        $this->name_title = Auth::user()->doctor->name_title;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if ($this->name_title != '' || $this->phone != '') {
            $this->updateDoctorInformation($user);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'max:10'],
            'age' => ['required', 'int', 'min:1'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        if ($validated['avatar'] != null) {
            $validated['avatar'] = $this->avatar->store('img/avatars', 'public');
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Session::flash('status', ['message' => 'Profile berhasil diubah.', 'success' => true]);

        $this->js(
            "window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });"
        );

//        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updateDoctorInformation($user): void
    {
        $validated = $this->validate([
            'name_title' => ['nullable', 'string', 'max:225'],
            'phone' => ['nullable', 'string', 'max:225'],
        ]);

        $user->doctor->update($validated);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.main-heading', ['title' => 'Profile'])

    <x-settings.layout heading="{{ __('Profile') }}" subheading="{{ __('Ubah profile') }}">
        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <flux:input wire:model="name" label="{{ __('Nama') }}" type="text" name="name" required
                        autocomplete="name"/>

            <div>
                <flux:input wire:model="email" label="{{ __('Email') }}" type="email" name="email" required
                            autocomplete="email"/>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-red-800">
                            {{ __('Email anda belum diverifikasi') }}

                            <button
                                    wire:click.prevent="resendVerificationNotification"
                                    class="rounded-md text-sm text-blue-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Klik disini untuk mengirim ulang email verifikasi.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <flux:input type="number" label="Usia" name="age" wire:model="age"></flux:input>

            <flux:select wire:model="gender" placeholder="Pilih gender..." label="Gender">
                @foreach(UserGender::cases() as $gender)
                    <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="file" label="Avatar" wire:model="avatar"></flux:input>

            @if($avatar_url || $avatar)
                <div class="flex justify-center items-center">
                    <img
                            src="{{ $avatar ? $avatar->temporaryUrl() : ($avatar_url ? asset('storage/' . $avatar_url) : '') }}"
                            alt="{{ $avatar ? 'preview-avatar' : 'avatar' }}" class="w-20 h-20 object-cover rounded-md">
                </div>
            @endif

            <flux:input type="text" name="name_title" wire:model="name_title" label="Gelar"></flux:input>

            <flux:input type="text" name="phone" wire:model="phone" mask="9999 9999 9999"
                        label="Telepon"></flux:input>

            <flux:input readonly variant="filled" wire:model="registered_year"
                        label="Tahun terdaftar HIMPSI"></flux:input>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Simpan') }}</flux:button>
                </div>

                {{--                <x-action-message class="me-3 text-green-600" on="profile-updated">--}}
                {{--                    {{ __('Tersimpan.') }}--}}
                {{--                </x-action-message>--}}
            </div>
        </form>

        {{--        <livewire:settings.delete-user-form />--}}
    </x-settings.layout>
</section>
