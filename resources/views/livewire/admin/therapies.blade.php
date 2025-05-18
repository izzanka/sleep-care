<?php

use App\Models\Therapy;
use App\Service\TherapyService;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function with()
    {
        $therapies = Therapy::latest()->paginate(15);

        return [
            'therapies' => $therapies,
        ];
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Terapi'])

        <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
            <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                <tr class="border-b">
                    <th class="px-6 py-3 text-left font-medium">No</th>
                    <th class="px-6 py-3 text-left font-medium">Psikolog</th>
                    <th class="px-6 py-3 text-left font-medium">Pasien</th>
                    <th class="px-6 py-3 text-left font-medium">Tanggal Mulai</th>
                    <th class="px-6 py-3 text-left font-medium">Tanggal Selesai</th>
                    <th class="px-6 py-3 text-left font-medium">Biaya Jasa Psikolog</th>
                    <th class="px-6 py-3 text-left font-medium">Biaya Jasa Aplikasi</th>
                    <th class="px-6 py-3 text-left font-medium">Status</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($therapies as $therapy)
                    <tr>
                        <td class="px-6 py-4 text-center">{{ ($therapies->currentPage() - 1) * $therapies->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4">{{$therapy->doctor->user->name}}</td>
                        <td class="px-6 py-4">{{$therapy->patient->name}}</td>
                        <td class="px-6 py-4">{{$therapy->start_date->format('d/m/Y')}}</td>
                        <td class="px-6 py-4">{{$therapy->end_date->format('d/m/Y')}}</td>
                        <td class="px-6 py-4">@currency($therapy->doctor_fee)</td>
                        <td class="px-6 py-4">@currency($therapy->application_fee)</td>
                        <td class="px-6 py-4">{{$therapy->status->label()}}</td>
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

        <div class="mt-6">
            {{$therapies->links()}}
        </div>
    </section>
</div>
