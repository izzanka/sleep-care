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
        $this->therapies = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::COMPLETED->value);
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Riwayat Terapi'])
    @if($therapies->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto w-full text-sm rounded-lg border overflow-hidden">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-3 text-center">No</th>
                    <th class="px-6 py-3 text-left">Tanggal Mulai</th>
                    <th class="px-6 py-3 text-left">Tanggal Selesai</th>
                    <th class="px-6 py-3 text-left">Biaya Jasa Psikolog</th>
                    <th class="px-6 py-3 text-left">Biaya Jasa Aplikasi</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($therapies as $therapy)
                    <tr>
                        <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $therapy->start_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">{{ $therapy->end_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">@currency($therapy->doctor_fee)</td>
                        <td class="px-6 py-4">@currency($therapy->application_fee)</td>
                        <td class="px-6 py-4">{{ $therapy->status->label() }}</td>
                        <td class="px-6 py-4">
                            <flux:button size="xs" variant="primary" wire:navigate
                                         :href="route('doctor.therapies.completed.detail', $therapy->id)">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="7" class="px-6 py-4 text-gray-500 dark:text-gray-400">
                            Belum ada terapi
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @else
        <flux:heading>
            Belum ada riwayat terapi
        </flux:heading>
    @endif
</section>

