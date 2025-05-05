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
        <div class="overflow-x-auto mt-4">
            <table class="min-w-[800px] w-full text-sm text-left">
                <thead>
                <tr class="text-center">
                    <th class="border p-3">No</th>
                    <th class="border p-3">ID</th>
                    <th class="border p-3">Tanggal Mulai</th>
                    <th class="border p-3">Tanggal Selesai</th>
                    <th class="border p-3">Biaya Jasa Psikolog</th>
                    <th class="border p-3">Biaya Jasa Aplikasi</th>
                    <th class="border p-3">Status</th>
                    <th class="border p-3">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($therapies as $therapy)
                    <tr>
                        <td class="border p-3 text-center">{{ $loop->iteration }}</td>
                        <td class="border p-3 text-center">{{ $therapy->id }}</td>
                        <td class="border p-3">{{ $therapy->start_date->isoFormat('D MMMM Y') }}</td>
                        <td class="border p-3">{{ $therapy->end_date->isoFormat('D MMMM Y') }}</td>
                        <td class="border p-3">@currency($therapy->doctor_fee)</td>
                        <td class="border p-3">@currency($therapy->application_fee)</td>
                        <td class="border p-3">{{ $therapy->status->label() }}</td>
                        <td class="border p-3 text-center">
                            <flux:button size="xs" wire:navigate :href="route('doctor.therapies.completed.detail', $therapy->id)">
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

