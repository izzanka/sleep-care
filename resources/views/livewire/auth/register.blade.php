<?php

use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Models\Doctor;
use App\Models\General;
use App\Models\User;
use App\Notifications\RegisteredUser;
use App\Service\HimpsiService;
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
    public bool $is_himpsi;

    public function mount()
    {
        $this->is_himpsi = General::value('is_himpsi');
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'gender' => ['required', 'string', 'max:10'],
        ]);

        if($this->is_himpsi){
            $himpsiData = $this->verifyHimpsiAccount($validated['email']);
        }else{
            $himpsiData = [
                'registered_year' => '2000',
                'phone' => null,
            ];
        }

        $user = $this->createUser($validated);
        $this->createDoctorProfile($user->id, $himpsiData);
        $this->notifyAdmin($user);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    protected function verifyHimpsiAccount(string $email)
    {
        $himpsiService = new HimpsiService();
        $result = $himpsiService->get($email);

        if ($result === false) {
            throw ValidationException::withMessages([
                'email' => __('Proses verifikasi akun HIMPSI gagal, Silahkan coba ulangi lagi nanti.'),
            ]);
        }

        if (empty($result)) {
            throw ValidationException::withMessages([
                'email' => __('Akun HIMPSI tidak ditemukan'),
            ]);
        }

        return $result;
    }

    protected function createUser(array $validated)
    {
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = UserRole::DOCTOR->value;

        return User::create($validated);
    }

    protected function createDoctorProfile(int $userID, array $himpsiData)
    {
        Doctor::create([
            'user_id' => $userID,
            'registered_year' => $himpsiData['registered_year'],
            'phone' => $himpsiData['phone'],
        ]);
    }

    protected function notifyAdmin(User $user)
    {
        $admin = User::where('role', UserRole::ADMIN->value)->first();

        if ($admin) {
            $admin->notify(new RegisteredUser($user));
        }
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
            label="{{ __('Nama dengan gelar') }}"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="name"
            placeholder="Nama dengan gelar"
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

        @if($this->is_himpsi)
            <flux:description>Pastikan nama dan email anda sudah terdaftar di
                <flux:link href="https://himpsi.or.id/" target="_blank">HIMPSI</flux:link>
            </flux:description>
        @endif

        {{--        <div class="flex items-center justify-end">--}}
        {{--            <flux:button variant="primary" class="w-full">--}}
        {{--                Cek akun HIMPSI--}}
        {{--            </flux:button>--}}
        {{--        </div>--}}

        <flux:select wire:model="gender" placeholder="Pilih jenis kelamin..." label="Jenis Kelamin">
            @foreach(UserGender::cases() as $gender)
                <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
            @endforeach
        </flux:select>

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
