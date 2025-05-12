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
    protected UserService $userService
    use WithPagination;

    #[Url]
    public string $search = '';

    public ?int $filterMinAge = null;
    public ?int $filterMaxAge = null;
    public ?string $filterGender = null;
    public ?string $filterProblem = null;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getPatients()
    {
        $query = User::query();

        if ($this->search !== '') {
            $this->resetPage();
            $query = User::search($this->search)->where('role', UserRole::PATIENT->value);
        } else {
            $query->where('role', UserRole::PATIENT->value);
        }

        $query = $this->applyFilters($query);

        return $query->latest()->paginate(15);
    }

    protected function applyFilters($query)
    {
        return $query
            ->when($this->filterMinAge, fn($q) => $q->where('age', '>=', $this->filterMinAge))
            ->when($this->filterMaxAge, fn($q) => $q->where('age', '<=', $this->filterMaxAge))
            ->when($this->filterGender, fn($q) => $q->where('gender', $this->filterGender));
    }

    public function filter()
    {
        $this->validate([
            'filterMinAge' => ['nullable', 'min:1', 'max:100', 'int'],
            'filterMaxAge' => ['nullable', 'min:1', 'max:100', 'int'],
            'filterGender' => ['nullable', 'string'],
        ]);

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterMinAge', 'filterMaxAge', 'filterGender']);
        $this->resetValidation(['filterMinAge', 'filterMaxAge', 'filterGender']);
    }

    public function destroyPatient(int $patientID)
    {
        $patient = $this->userService->get(role: UserRole::PATIENT->value, id: $patientID)->first();

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

        <div x-data="{showFilter: false}">
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari pasien" wire:model.live="search"/>
                {{--                <flux:button class="ml-2" variant="primary">Cari</flux:button>--}}
            </div>
            <flux:button @click="showFilter = !showFilter" class="mt-4 w-full">
                Filter
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-4 h-4 transition-transform duration-300"
                    :class="showFilter ? 'rotate-180' : ''"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </flux:button>
            <flux:separator class="mt-4 mb-4"/>
            <div x-show="showFilter" x-transition>
                <form wire:submit="filter">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                        <div>
                            <flux:input label="Usia Minimal" wire:model="filterMinAge" placeholder="1"/>
                        </div>
                        <div>
                            <flux:input label="Usia Maksimal" wire:model="filterMaxAge" placeholder="100"/>
                        </div>

                        <div>
                            <flux:select label="Gender" wire:model="filterGender">
                                <flux:select.option value="">Semua</flux:select.option>
                                @foreach(UserGender::cases() as $gender)
                                    <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:select label="Gender" wire:model="filterGender">
                                <flux:select.option value="">Semua</flux:select.option>
                                @foreach(UserGender::cases() as $gender)
                                    <flux:select.option :value="$gender">{{$gender->label()}}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                    </div>
                    <flux:button variant="primary" type="submit">Filter</flux:button>
                    <flux:button class="ms-2" variant="danger" wire:click="resetFilter">Reset</flux:button>
                </form>
                <flux:separator class="mt-4 mb-4"/>
            </div>
        </div>

        <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
            <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                <tr class="border-b">
                    <th class="px-6 py-3 text-left font-medium">Aksi</th>
                    <th class="px-6 py-3 text-left font-medium">No</th>
                    <th class="px-6 py-3 text-left font-medium">Nama</th>
                    <th class="px-6 py-3 text-left font-medium">Email</th>
                    <th class="px-6 py-3 text-left font-medium">Usia</th>
                    <th class="px-6 py-3 text-left font-medium">Gender</th>
                    <th class="px-6 py-3 text-left font-medium">Gangguan Lainnya</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <flux:button size="xs" icon="trash" variant="danger"
                                             wire:click="destroyPatient({{$user->id}})"
                                             wire:confirm="Apa anda yakin ingin menghapus pasien ini?"></flux:button>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4">{{$user->name}}</td>
                        <td class="px-6 py-4">{{$user->email}}</td>
                        <td class="px-6 py-4">{{$user->age}}</td>
                        <td class="px-6 py-4">{{$user->gender->label()}}</td>
                        <td class="px-6 py-4">
                            @forelse(json_decode($user->problems) as $problem)
                                {{ Problem::tryFrom($problem)->label() }},
                            @empty
                                -
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="8" class="px-6 py-4 text-gray-500 dark:text-gray-400">
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
