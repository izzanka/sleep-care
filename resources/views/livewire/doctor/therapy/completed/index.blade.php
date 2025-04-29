<?php

use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;

    public $therapies;

    public function boot(TherapyService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapies = $this->therapyService->find(doctorId: $doctorId, status: TherapyStatus::COMPLETED->value);
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Riwayat'])
    @if($therapies->isNotEmpty())
        <div class="overflow-x-auto border mt-4">
            <table class="min-w-[800px] w-full text-sm text-left">
                <thead>
                <tr class="text-center">
                    <th class="border p-3">No</th>
                    <th class="border p-3">ID</th>
                    <th class="border p-3">Pasien</th>
                    <th class="border p-3">Waktu</th>
                    <th class="border p-3">Status</th>
                    <th class="border p-3">Rating</th>
                    <th class="border p-3">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($therapies as $therapy)
                    <tr>
                        <td class="border p-3 text-center">{{ $loop->iteration }}</td>
                        <td class="border p-3 text-center">{{ $therapy->id }}</td>
                        <td class="border p-3">{{ $therapy->patient->name }}</td>
                        <td class="border p-3">{{ $therapy->start_date->format('d M Y') }} - {{ $therapy->end_date->format('d M Y') }}</td>
                        <td class="border p-3">{{ $therapy->status->label() }}</td>
                        <td class="border p-3 text-center">4/5</td>
                        <td class="border p-3 text-center">
                            <flux:button size="xs">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <flux:heading>
            Belum ada riwayat terapi
        </flux:heading>
    @endif
</section>

