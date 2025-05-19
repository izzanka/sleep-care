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
    @include('partials.main-heading', ['title' => 'Riwayat'])
    @if($therapies->isNotEmpty())
        <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
            <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                <tr class="border-b">
                    <th class="px-6 py-3 text-left font-medium">No</th>
                    <th class="px-6 py-3 text-left font-medium">ID</th>
                    <th class="px-6 py-3 text-left font-medium">Tanggal Mulai</th>
                    <th class="px-6 py-3 text-left font-medium">Tanggal Selesai</th>
                    <th class="px-6 py-3 text-left font-medium">Biaya Jasa Psikolog</th>
                    <th class="px-6 py-3 text-left font-medium">Biaya Jasa Aplikasi</th>
                    <th class="px-6 py-3 text-left font-medium">Status</th>
                    <th class="px-6 py-3 text-left font-medium">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($therapies as $therapy)
                    <tr>
                        <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{$therapy->id}}</td>
                        <td class="px-6 py-4">{{$therapy->start_date->format('d/m/Y')}}</td>
                        <td class="px-6 py-4">{{$therapy->end_date->format('d/m/Y')}}</td>
                        <td class="px-6 py-4">@currency($therapy->doctor_fee)</td>
                        <td class="px-6 py-4">@currency($therapy->application_fee)</td>
                        <td class="px-6 py-4">{{$therapy->status->label()}}</td>
                        <td class="px-6 py-4">
                            <flux:button size="xs" wire:navigate
                                         :href="route('doctor.therapies.completed.detail', $therapy->id)">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="8" class="px-6 py-4 text-gray-500 dark:text-gray-400">
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

