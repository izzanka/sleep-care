<?php

use App\Enum\Problem;
use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Models\Doctor;
use App\Models\User;
use App\Service\UserService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Url;
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

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getPatients()
    {
        $query = User::query();
        $query->withTrashed();

        if ($this->search !== '') {
            $this->resetPage();
            $query = User::search($this->search)->where('role', UserRole::PATIENT->value);
        } else {
            $query->where('role', UserRole::PATIENT->value);
        }
        return $query->latest()->paginate(15);
    }

    public function editPatient(int $patientId)
    {
        $patient = $this->userService->get(role: UserRole::PATIENT->value, id: $patientId)->first();

        if (!$patient) {
            session()->flash('status', ['message' => 'Pasien tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $this->fillPatientData($patient);

        $this->modal('editPatient')->show();
    }

    public function fillPatientData($patient)
    {
        $this->id = $patient->id;
        $this->name = $patient->name;
        $this->email = $patient->email;
        $this->is_active = $patient->is_active;
    }

    public function updatePatient(int $patientId)
    {
        $validated = $this->validate([
            'is_active' => ['required', 'boolean']
        ]);

        $patient = $this->userService->get(role: UserRole::PATIENT->value, id: $patientId)->first();

        if (!$patient) {
            session()->flash('status', ['message' => 'Pasien tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $patient->update($validated);

        session()->flash('status', ['message' => 'Data pasien berhasil diubah.', 'success' => true]);

        $this->redirectRoute('admin.users.patient');
    }

    public function deletePatient(int $patientId)
    {
        $patient = $this->userService->get(role: UserRole::PATIENT->value, id: $patientId)->first();

        if (!$patient) {
            session()->flash('status', ['message' => 'Pasien tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $patient->delete();

        session()->flash('status', ['message' => 'Pasien berhasil dihapus.', 'success' => true]);

        $this->redirectRoute('admin.users.patient');
    }

    public function with()
    {
        return [
            'users' => $this->getPatients(),
        ];
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Pasien'])
        <div class="mb-5">
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari pasien berdasarkan nama atau email" wire:model.live="search"/>
            </div>
{{--            <flux:separator class="mt-4 mb-4"/>--}}
        </div>

        <flux:modal name="editPatient" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6">
                <form wire:submit="updatePatient({{$id}})">
                    <div>
                        <flux:heading size="lg">Ubah Data Pasien</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:input wire:model="name" label="Nama" readonly></flux:input>
                        <flux:input wire:model="email" label="Email" readonly></flux:input>
                    </div>

                    <flux:separator class="mt-4 mb-4"></flux:separator>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:field>
                            <flux:label>Aktif</flux:label>
                            <flux:switch wire:model="is_active" />
                            <flux:error name="is_active" />
                        </flux:field>
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <div class="overflow-x-auto rounded-lg">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr>
                    <th class=" px-4 py-2 text-left font-medium">Aksi</th>
                    <th class=" px-4 py-2 text-left font-medium">No</th>
                    <th class=" px-4 py-2 text-left font-medium">Aktif</th>
                    <th class=" px-4 py-2 text-left font-medium">Nama</th>
                    <th class=" px-4 py-2 text-left font-medium">Email</th>
                    <th class=" px-4 py-2 text-left font-medium">Usia</th>
                    <th class=" px-4 py-2 text-left font-medium">Jenis Kelamin</th>
                    <th class=" px-4 py-2 text-left font-medium">Gangguan Lainnya</th>
                    <th class=" px-4 py-2 text-left font-medium">Dibuat Pada</th>
                    <th class=" px-4 py-2 text-left font-medium">Diperbarui Pada</th>
                    <th class=" px-4 py-2 text-left font-medium">Dihapus Pada</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($users as $user)
                    <tr wire:key="{{$user->id}}">
                        <td class=" px-4 py-2">
                            <div class="flex space-x-2">
                                <flux:button size="xs" variant="primary" icon="pencil-square" wire:click="editPatient({{$user->id}})"></flux:button>
                                <flux:button size="xs" icon="trash" variant="danger" wire:click="deletePatient({{$user->id}})" wire:confirm="Apa anda yakin ingin menghapus pasien ini?"></flux:button>
                            </div>
                        </td>
                        <td class=" px-4 py-2 text-center">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td class=" px-4 py-2 text-center">{{$user->is_active ? 'Ya' : 'Tidak'}}</td>
                        <td class=" px-4 py-2">{{$user->name}}</td>
                        <td class=" px-4 py-2">{{$user->email}}</td>
                        <td class=" px-4 py-2 text-center">{{$user->age}}</td>
                        <td class=" px-4 py-2">{{$user->gender->label()}}</td>
                        <td class=" px-4 py-2">
                            @forelse(json_decode($user->problems) as $problem)
                                {{ Problem::tryFrom($problem)->label() }},
                            @empty
                                -
                            @endforelse
                        </td>
                        <td class="px-4 py-2">{{$user->created_at->format('d/m/Y H:i')}}</td>
                        <td class="px-4 py-2">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-4 py-2">{{$user->deleted_at ? $user->deleted_at->format('d/m/Y H:i') : '-'}}</td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="11" class=" px-4 py-2 text-gray-500 dark:text-gray-400">
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
