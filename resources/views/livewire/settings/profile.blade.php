<?php

use App\Enum\UserGender;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    public ?string $about = '';
    public ?string $graduate = '';
    public int $age;
    public int $registered_year;
    public $avatar;
    public $avatar_url;
    public $user;

    public function mount(): void
    {
        $this->user = auth()->user();

        $this->fillUserFields();
        $this->fillDoctorFields();
    }

    public function updateProfileInformation()
    {
        if ($this->shouldUpdateDoctorInfo()) {
            $this->updateDoctorInformation();
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
                Rule::unique(User::class)->ignore(Auth::id()),
            ],
        ]);

        if ($this->avatar) {
            $validated['avatar'] = $this->avatar->store('img/avatars', 'public');
        }

        $this->user->fill($validated);

        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        session()->flash('status', ['message' => 'Profile berhasil diubah.', 'success' => true]);

        $this->redirectRoute('settings.profile');
    }

    protected function fillUserFields()
    {
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->gender = $this->user->gender->value;
        $this->age = $this->user->age;
        $this->avatar_url = $this->user->avatar;
    }

    protected function fillDoctorFields()
    {
        $this->phone = $this->user->doctor->phone;
        $this->registered_year = $this->user->doctor->registered_year;
        $this->name_title = $this->user->doctor->name_title;
        $this->about = $this->user->doctor->about;
        $this->graduate = $this->user->doctor->graduate;
    }

    protected function shouldUpdateDoctorInfo()
    {
        return $this->name_title !== '' || $this->phone !== '';
    }

    public function updateDoctorInformation()
    {
        $validated = $this->validate([
            'name_title' => ['nullable', 'string', 'max:225'],
            'phone' => ['nullable', 'string', 'max:225'],
            'about' => ['nullable', 'string', 'max:225'],
            'graduate' => ['nullable', 'string', 'max:225'],
        ]);

        $this->user->doctor->update($validated);
    }

    public function resendVerificationNotification(): void
    {
        if ($this->user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $this->user->sendEmailVerificationNotification();

        session()->flash('status', 'verification-link-sent');
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
            </div>

            <flux:input type="number" label="Usia" name="age" wire:model="age"></flux:input>

            <flux:select wire:model="gender" placeholder="Pilih gender..." label="Gender">
                @foreach(UserGender::cases() as $gender)
                    <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="text" label="Lulusan" name="graduate" wire:model="graduate" placeholder="-"></flux:input>

            <flux:textarea
                label="Tentang"
                placeholder="Ceritakan sedikit tentang diri anda"
                wire:model="about"
            />

            <flux:input type="file" label="Avatar" wire:model="avatar"></flux:input>

            @if($avatar_url || $avatar)
                <div class="flex justify-center items-center">
                    <img
                        src="{{ $avatar ? $avatar->temporaryUrl() : ($avatar_url ? asset('storage/' . $avatar_url) : '') }}"
                        alt="{{ $avatar ? 'preview-avatar' : 'avatar' }}" class="w-20 h-20 object-cover rounded-md">
                </div>
            @endif

            <flux:input type="text" name="name_title" wire:model="name_title" label="Gelar" placeholder="-"></flux:input>

            <flux:input type="text" name="phone" wire:model="phone" mask="9999 9999 9999"
                        label="Telepon" placeholder="-"></flux:input>

            <flux:input readonly variant="filled" wire:model="registered_year"
                        label="Tahun terdaftar di HIMPSI"></flux:input>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Simpan') }}</flux:button>
                </div>
            </div>
        </form>

    </x-settings.layout>
</section>
