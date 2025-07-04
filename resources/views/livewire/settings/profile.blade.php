<?php

use App\Enum\UserGender;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $gender = '';
    public ?string $phone = '';
    public ?string $about = '';
    public ?string $graduated_from = '';
    public ?int $age = null;
    public int $registered_year;
    public $avatar;
    public $avatar_url;
    public $user;
    public $is_available;

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

//        if ($this->avatar) {
//            $validated['avatar'] = $this->avatar->store('img/avatars', 'public');
//        }

        if ($this->avatar) {
            $validated['avatar'] = $this->avatar->store('img/avatars', 'public');
        } else {
            // Preserve old avatar if no new one is uploaded
            $validated['avatar'] = $this->user->avatar;
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
        $this->about = $this->user->doctor->about;
        $this->graduated_from = $this->user->doctor->graduated_from;
        $this->is_available = (bool) $this->user->doctor->is_available;
    }

//    protected function shouldUpdateDoctorInfo()
//    {
//        return $this->phone !== '' || $this->about !== '' || $this->graduated_from != '' || $this->is_available;
//    }

    protected function shouldUpdateDoctorInfo()
    {
        return filled($this->phone)
            || filled($this->about)
            || filled($this->graduated_from)
            || !is_null($this->is_available);
    }

    public function updateDoctorInformation()
    {
        $validated = $this->validate([
            'phone' => ['nullable', 'string', 'max:225'],
            'about' => ['nullable', 'string', 'max:225'],
            'graduated_from' => ['nullable', 'string', 'max:225'],
            'is_available' => ['required', 'boolean'],
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

            <flux:select wire:model="gender" placeholder="Pilih Jenis Kelamin..." label="Jenis Kelamin">
                @foreach(UserGender::cases() as $gender)
                    <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="number" label="Usia" name="age" wire:model="age" placeholder="-"></flux:input>

            <flux:input type="text" label="Lulusan" name="graduated_from" wire:model="graduated_from" placeholder="-"></flux:input>

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
                        alt="{{ $avatar ? 'preview-avatar' : 'avatar' }}" class="w-40 h-40 object-cover rounded-md">
                </div>
            @endif

            <flux:input type="text" name="phone" wire:model="phone" mask="9999 9999 9999"
                        label="Telepon" placeholder="-"></flux:input>

            <flux:input readonly variant="filled" wire:model="registered_year"
                        label="Tahun terdaftar di HIMPSI"></flux:input>

            <div class="w-full">
                <flux:checkbox wire:model="is_available" label="Tersedia"
                               description="Jika diaktifkan, anda dapat ditemukan dan dipesan oleh pasien untuk menjalani terapi."
                />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Simpan') }}</flux:button>
                </div>
            </div>
        </form>

    </x-settings.layout>
</section>
