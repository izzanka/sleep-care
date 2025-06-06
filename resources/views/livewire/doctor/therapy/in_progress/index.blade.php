
<?php

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';
    public $doctorId;

    public function mount()
    {
        $this->doctorId = auth()->user()->doctor->id;
    }

    public function getTherapies()
    {
        return Therapy::query()
            ->where('doctor_id', $this->doctorId)
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->when($this->search, function (Builder $query) {
                $query->whereHas('patient', function (Builder $q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(15);
    }

    public function with()
    {
        $therapies = $this->getTherapies();
        return [
            'therapies' => $therapies,
        ];
    }

}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Daftar Terapi'])

    <div class="mb-4">
        <div class="flex items-center">
            <flux:input icon="magnifying-glass" placeholder="Cari terapi berdasarkan nama pasien" wire:model.live="search"/>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-auto w-full text-sm rounded-lg border overflow-hidden">
            <thead class="bg-blue-400 dark:bg-blue-600 text-white">
            <tr class="text-left">
                <th class="px-4 py-2">Aksi</th>
                <th class="px-4 py-2 text-center">No</th>
                <th class="px-4 py-2">Pasien</th>
                <th class="px-4 py-2">Tanggal Mulai</th>
                <th class="px-4 py-2">Tanggal Selesai</th>
                <th class="px-4 py-2">Biaya Jasa Psikolog</th>
                <th class="px-4 py-2">Biaya Jasa Aplikasi</th>
                <th class="px-4 py-2">Status</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse($therapies as $therapy)
                <tr class="text-left">
                    <td class="px-4 py-2">
                        <flux:button size="xs" variant="primary" wire:navigate
                                     href="{{route('doctor.therapies.in_progress.detail', $therapy->id)}}">
                            Detail
                        </flux:button>
                    </td>
                    <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2">{{ $therapy->patient->name }}</td>
                    <td class="px-4 py-2">{{ $therapy->start_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $therapy->end_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">@currency($therapy->doctor_fee)</td>
                    <td class="px-4 py-2">@currency($therapy->application_fee)</td>
                    <td class="px-4 py-2">{{ $therapy->status->label() }}</td>
                </tr>
            @empty
                <tr class="text-center">
                    <td colspan="8" class="px-4 py-2 text-dark dark:text-white">
                        <flux:heading class="mt-2">
                            Terapi tidak ditemukan.
                        </flux:heading>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        {{$therapies->links()}}
    </div>
</section>


