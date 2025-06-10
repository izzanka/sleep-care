<?php

use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Models\User;
use App\Service\UserService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    protected UserService $userService;

    use WithPagination;

    #[Url]
    public string $search = '';

    public int $id;
    public string $name;
    public string $email;
    public bool $is_active;
    public ?string $avatar;

    public ?string $graduated_from;
    public ?string $phone;
    public ?string $about;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getDoctors()
    {
        $query = User::query();

        if ($this->search !== '') {
            $this->resetPage();
            $query = User::search($this->search)->where('role', UserRole::DOCTOR->value);
        } else {
            $query->where('role', UserRole::DOCTOR->value);
        }

        return $query->latest()->paginate(15);
    }

    public function editDoctor(int $doctorId)
    {
        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorId)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $this->fillDoctorData($doctor);

        $this->modal('editDoctor')->show();
    }

    public function fillDoctorData($doctor)
    {
        $this->id = $doctor->id;
        $this->name = $doctor->name;
        $this->email = $doctor->email;
        $this->is_active = $doctor->is_active;
        $this->avatar = $doctor->avatar;
        $this->graduated_from = $doctor->doctor->graduated_from;
        $this->phone = $doctor->doctor->phone;
        $this->about = $doctor->doctor->about;
    }

    public function deleteAvatarDoctor(int $doctorId)
    {
        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorId)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        if (Storage::disk('public')->exists($this->avatar)) {
            Storage::disk('public')->delete($this->avatar);
            $doctor->update(['avatar' => null]);
            session()->flash('status', ['message' => 'Avatar psikolog berhasil dihapus.', 'success' => true]);
        } else {
            session()->flash('status', ['message' => 'Avatar psikolog tidak ditemukan.', 'success' => false]);
        }

        $this->redirectRoute('admin.users.doctor');
    }

    public function updateDoctor(int $doctorId)
    {
        $validated = $this->validate([
            'is_active' => ['required', 'boolean'],
            'graduated_from' => ['nullable', 'string', 'max:225'],
            'phone' => ['nullable', 'string'],
            'about' => ['nullable', 'string', 'max:225']
        ]);

        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorId)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        if ($doctor->is_therapy_in_progress && !$validated['is_active']) {
            session()->flash('status', ['message' => 'Psikolog sedang melakukan terapi.', 'success' => false]);
            $this->modal('editDoctor')->close();
        } else {
            $doctor->update([
                'is_active' => $validated['is_active']
            ]);

            if (!$validated['is_active']) {
                $validated['is_available'] = false;
            }

            unset($validated['is_active']);
            $doctor->doctor->update($validated);

            session()->flash('status', ['message' => 'Data psikolog berhasil diubah.', 'success' => true]);

            $this->redirectRoute('admin.users.doctor');
        }
    }

    public function deleteDoctor(int $doctorID)
    {
        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorID)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        if ($doctor->is_therapy_in_progress) {
            session()->flash('status', ['message' => 'Psikolog sedang melakukan terapi.', 'success' => false]);
            return;
        }

        $doctor->delete();
        $doctor->doctor->delete();

        session()->flash('status', ['message' => 'Psikolog berhasil dihapus.', 'success' => true]);

        $this->redirectRoute('admin.users.doctor');
    }

    public function with()
    {
        return [
            'users' => $this->getDoctors(),
        ];
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Daftar Psikolog'])

        <div class="mb-5">
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari psikolog berdasarkan nama atau email"
                            wire:model.live="search"/>
            </div>
            {{--            <flux:separator class="mt-4 mb-4"/>--}}
        </div>

        <flux:modal name="editDoctor" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6">
                <form wire:submit="updateDoctor({{$id}})">
                    <div>
                        <flux:heading size="lg">Ubah Data Psikolog</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="name" label="Nama" readonly></flux:input>
                        <flux:input wire:model="email" label="Email" readonly></flux:input>
                    </div>

                    <flux:separator class="mt-4 mb-4"></flux:separator>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="graduated_from" label="Lulusan" placeholder="-"></flux:input>
                        <flux:input wire:model="phone" label="Telepon" placeholder="-"></flux:input>
                    </div>

                    <div class="mt-4 mb-4">
                        <flux:textarea wire:model="about" label="Tentang" placeholder="-"></flux:textarea>
                    </div>

                    @if($avatar != null)
                        <flux:heading>Avatar</flux:heading>
                        <div class="mt-4 mb-4 flex justify-center">
                            <img src="{{asset('storage/' . $avatar)}}" alt="Avatar"
                                 class="w-full max-w-xs h-auto rounded-md object-cover">
                        </div>
                        <flux:button variant="danger" class="w-full" wire:click="deleteAvatarDoctor({{$id}})"
                                     wire:confirm="Apa anda yakin ingin menghapus avatar psikolog ini?">Hapus avatar
                        </flux:button>
                    @endif

                    <div class="mt-4 mb-4">
                        {{--                        <flux:field>--}}
                        {{--                            <flux:label>Aktif</flux:label>--}}
                        {{--                            <flux:switch wire:model="is_active" description="Jika dinonaktifkan, akun psikolog tidak akan dapat digunakan untuk login."/>--}}
                        {{--                            <flux:error name="is_active"/>--}}
                        {{--                        </flux:field>--}}
                        <flux:switch wire:model="is_active" label="Aktif"
                                     description="Jika dinonaktifkan, akun psikolog tidak akan dapat digunakan untuk login."/>
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <div class="overflow-x-auto rounded-lg">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr class="text-left">
                    <th class="px-4 py-2 font-medium">Aksi</th>
                    <th class="px-4 py-2 font-medium">No</th>
                    <th class="px-4 py-2 font-medium">Aktif</th>
                    <th class=" px-4 py-2 text-left font-medium">Sedang Melakukan Terapi</th>
                    <th class="px-4 py-2 font-medium">Nama</th>
                    <th class="px-4 py-2 font-medium">Email</th>
                    <th class="px-4 py-2 font-medium">Telepon</th>
                    <th class="px-4 py-2 font-medium">Usia</th>
                    <th class="px-4 py-2 font-medium">Jenis Kelamin</th>
                    <th class="px-4 py-2 font-medium">Lulusan</th>
                    <th class="px-4 py-2 font-medium">Terdaftar HIMPSI</th>
                    <th class="px-4 py-2 font-medium">Dibuat Pada</th>
                    <th class="px-4 py-2 font-medium">Diperbarui Pada</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($users as $user)
                    <tr wire:key="{{$user->id}}">
                        <td class="px-4 py-2">
                            <div class="flex space-x-2">
                                <flux:button size="xs" variant="primary" icon="pencil-square"
                                             wire:click="editDoctor({{$user->id}})"></flux:button>
                                <flux:button size="xs" icon="trash" variant="danger"
                                             wire:click="deleteDoctor({{$user->id}})"
                                             wire:confirm="Apa anda yakin ingin menghapus psikolog ini?"></flux:button>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-2 text-center">{{$user->is_active ? 'Ya' : 'Tidak'}}</td>
                        <td class="px-4 py-2 text-center">{{$user->is_therapy_in_progress ? 'Ya' : 'Tidak'}}</td>
                        <td class="px-4 py-2">{{$user->name}}</td>
                        <td class="px-4 py-2">{{$user->email}}</td>
                        <td class="px-4 py-2">{{$user->doctor->phone ?: '-'}}</td>
                        <td class="px-4 py-2 text-center">{{$user->age ?? '-'}}</td>
                        <td class="px-4 py-2">{{$user->gender->label()}}</td>
                        <td class="px-4 py-2">{{$user->doctor->graduated_from ?: '-'}}</td>
                        <td class="px-4 py-2 text-center">{{$user->doctor->registered_year}}</td>
                        <td class="px-4 py-2">{{$user->created_at->format('d/m/Y H:i')}}</td>
                        <td class="px-4 py-2">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-2 text-center" colspan="13">
                            <flux:heading>Psikolog tidak ditemukan.</flux:heading>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{$users->links()}}
        </div>
    </section>
</div>
