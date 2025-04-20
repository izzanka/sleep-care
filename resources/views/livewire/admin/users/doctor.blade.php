<?php

use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public ?int $filterMinAge = null;
    public ?int $filterMaxAge = null;
    public ?string $filterGender = null;
    public ?bool $filterIsActive = null;

    public function getDoctors()
    {
//        $query = $this->search !== ''
//            ? User::search($this->search)->where('role', UserRole::DOCTOR->value)
//            : User::query()->where('role', UserRole::DOCTOR->value);

        $query = User::query();

        if ($this->search !== ''){
            $this->resetPage();
            $query = User::search($this->search)->where('role', UserRole::DOCTOR->value);
        }else{
            $query->where('role', UserRole::DOCTOR->value);
        }

        $query = $this->applyFilters($query);

        $users = $query->latest()->paginate(15);
        $users->load('doctor');

        return $users;
    }

    protected function applyFilters($query)
    {
        return $query
            ->when($this->filterMinAge, fn ($q) => $q->where('age', '>=', $this->filterMinAge))
            ->when($this->filterMaxAge, fn ($q) => $q->where('age', '<=', $this->filterMaxAge))
            ->when($this->filterGender, fn ($q) => $q->where('gender', $this->filterGender))
            ->when(is_bool($this->filterIsActive), fn ($q) => $q->where('is_active', $this->filterIsActive));
    }

    public function updatedFilterIsActive($value)
    {
        $this->filterIsActive = match ($value) {
            'true' => true,
            'false' => false,
            default => null,
        };
    }

    public function filter()
    {
        $this->validate([
            'filterMinAge' => ['nullable', 'integer', 'min:1', 'max:100'],
            'filterMaxAge' => ['nullable', 'integer', 'min:1', 'max:100'],
            'filterGender' => ['nullable', 'string'],
            'filterIsActive' => ['nullable', 'boolean'],
        ]);

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterMinAge', 'filterMaxAge', 'filterGender', 'filterIsActive']);
        $this->resetValidation(['filterMinAge', 'filterMaxAge', 'filterGender', 'filterIsActive']);
    }

    public function destroyDoctor(int $doctorID)
    {
        $doctor = User::find($doctorID);

        if (!$doctor) {
            Session::flash('status', ['message' => 'Psikolog tidak dapat ditemukan.', 'success' => false]);
            return;
        }

        $doctor->delete();

        Session::flash('status', ['message' => 'Psikolog berhasil dihapus.', 'success' => true]);

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

        <div x-data="{showFilter: false}">
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari psikolog" wire:model.live="search"/>
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
                            <flux:select label="Aktif" wire:model="filterIsActive">
                                <flux:select.option value="">Semua</flux:select.option>
                                <flux:select.option value="true">Ya</flux:select.option>
                                <flux:select.option value="false">Tidak</flux:select.option>
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
                    <th class="px-6 py-3 text-left font-medium">Aktif</th>
                    <th class="px-6 py-3 text-left font-medium">Nama</th>
                    <th class="px-6 py-3 text-left font-medium">Email</th>
                    <th class="px-6 py-3 text-left font-medium">Telepon</th>
                    <th class="px-6 py-3 text-left font-medium">Usia</th>
                    <th class="px-6 py-3 text-left font-medium">Gender</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($users as $user)
                    <tr wire:key="{{$user->id}}">
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <flux:button size="xs" icon="trash" variant="danger" wire:click="destroyDoctor({{$user->id}})" wire:confirm="Apa anda yakin ingin menghapus psikolog ini?"></flux:button>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <livewire:status :id="$user->id" :is_active="$user->is_active"
                                             :key="$user->id"></livewire:status>
                        </td>
                        <td class="px-6 py-4">{{$user->doctor->name_title ?? $user->name}}</td>
                        <td class="px-6 py-4">{{$user->email}}</td>
                        <td class="px-6 py-4">{{$user->doctor->phone}}</td>
                        <td class="px-6 py-4">{{$user->age}}</td>
                        <td class="px-6 py-4">{{$user->gender->label()}}</td>
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
