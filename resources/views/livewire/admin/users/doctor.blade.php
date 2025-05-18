<?php

use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Models\User;
use App\Service\UserService;
use Illuminate\Support\Facades\Session;
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

    public ?string $graduated_from;
    public ?string $phone;
    public ?string $about;
    public ?string $name_title;

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
        $doctor = $this->userService->get(role: UserRole::DOCTOR->value,id: $doctorId)->first();

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
        $this->name_title = $doctor->doctor->name_title;
        $this->graduated_from = $doctor->doctor->graduated_from;
        $this->phone = $doctor->doctor->phone;
        $this->about = $doctor->doctor->about;
    }

    public function updateDoctor(int $doctorId)
    {
        $validated = $this->validate([
            'is_active' => ['required', 'boolean'],
            'name_title' => ['nullable', 'string'],
            'graduated_from' => ['nullable', 'string', 'max:225'],
            'phone' => ['nullable', 'string'],
            'about' => ['nullable', 'string', 'max:225']
        ]);

        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorId)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $doctor->update(['is_active' => $validated['is_active']]);
        unset($validated['is_active']);
        $doctor->doctor->update($validated);

        session()->flash('status', ['message' => 'Data psikolog berhasil diubah.', 'success' => true]);

        $this->redirectRoute('admin.users.doctor');
    }

    public function deleteDoctor(int $doctorID)
    {
        $doctor = $this->userService->get(role: UserRole::DOCTOR->value, id: $doctorID)->first();

        if (!$doctor) {
            session()->flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
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
        @include('partials.main-heading', ['title' => 'Psikolog'])

        <div>
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari psikolog" wire:model.live="search"/>
            </div>
            <flux:separator class="mt-4 mb-4"/>
        </div>

        <flux:modal name="editDoctor" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6">
                <form wire:submit="updateDoctor({{$id}})">
                    <div>
                        <flux:heading size="lg">Ubah Data Psikolog</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="name" label="Nama" disabled></flux:input>
                        <flux:input wire:model="email" label="Email" disabled></flux:input>
                    </div>

                    <flux:separator class="mt-4 mb-4"></flux:separator>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="graduated_from" label="Lulusan" placeholder="-"></flux:input>
                        <flux:input wire:model="name_title" label="Nama Gelar" placeholder="-"></flux:input>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="phone" label="Telepon" placeholder="-"></flux:input>
                        <flux:field>
                            <flux:label>Aktif</flux:label>
                            <flux:switch wire:model="is_active" />
                            <flux:error name="is_active" />
                        </flux:field>
                    </div>

                    <div class="mt-4 mb-4">
                        <flux:textarea wire:model="about" label="Tentang" placeholder="-"></flux:textarea>
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
            <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                <tr class="border-b">
                    <th class="px-6 py-3 text-left font-medium">Aksi</th>
                    <th class="px-6 py-3 text-left font-medium">No</th>
                    <th class="px-6 py-3 text-left font-medium">Aktif</th>
                    <th class="px-6 py-3 text-left font-medium">Nama</th>
                    <th class="px-6 py-3 text-left font-medium">Email</th>
                    <th class="px-6 py-3 text-left font-medium">Telepon</th>
                    <th class="px-6 py-3 text-left font-medium">Usia</th>
                    <th class="px-6 py-3 text-left font-medium">Jenis Kelamin</th>
                    <th class="px-6 py-3 text-left font-medium">Lulusan</th>
                    <th class="px-6 py-3 text-left font-medium">Tahun Terdaftar di HIMPSI</th>
                    <th class="px-6 py-3 text-left font-medium">Dibuat Pada</th>
                    <th class="px-6 py-3 text-left font-medium">Diperbarui Pada</th>
                    <th class="px-6 py-3 text-left font-medium">Dihapus Pada</th>

                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($users as $user)
                    <tr wire:key="{{$user->id}}">
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <flux:button size="xs" icon="pencil-square" wire:click="editDoctor({{$user->id}})">
                                </flux:button>
                                <flux:button size="xs" icon="trash" variant="danger"
                                             wire:click="deleteDoctor({{$user->id}})"
                                             wire:confirm="Apa anda yakin ingin menghapus psikolog ini?"></flux:button>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 text-center">{{$user->is_active ? 'Ya' : 'Tidak'}}</td>
                        <td class="px-6 py-4">{{$user->doctor->name_title ?? $user->name}}</td>
                        <td class="px-6 py-4">{{$user->email}}</td>
                        <td class="px-6 py-4">{{$user->doctor->phone}}</td>
                        <td class="px-6 py-4 text-center">{{$user->age}}</td>
                        <td class="px-6 py-4">{{$user->gender->label()}}</td>
                        <td class="px-6 py-4">{{$user->doctor->graduated_from ?? '-'}}</td>
                        <td class="px-6 py-4 text-center">{{$user->doctor->registered_year}}</td>
                        <td class="px-6 py-4">{{$user->created_at->format('d/m/Y H:i')}}</td>
                        <td class="px-6 py-4">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-6 py-4">{{$user->deleted_at ? $user->deleted_at->format('d/m/Y H:i') : '-'}}</td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="13" class="px-6 py-4 text-gray-500 dark:text-gray-400">
                            Kosong
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
