<?php

use App\Enum\Problem;
use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;

    public $therapy;
    public $problems;

    public function boot(TherapyService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->find(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value)[0];
        if($this->therapy){
            $this->problems = $this->formatPatientProblems($this->therapy->patient->problems);
        }
    }

    protected function formatPatientProblems(?string $problems)
    {
        if (!$problems) {
            return '-';
        }

        return collect(json_decode($problems))
            ->map(fn($problem) => Problem::tryFrom($problem)?->label() ?? $problem)
            ->implode(', ');
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Halaman Utama'])
    {{--    <x-therapies.on-going-layout>--}}
    <div>
        @if($therapy)
            <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
                <div class="flex items-center space-x-2">
                    <flux:icon.document></flux:icon.document>
                    <flux:heading>
                        Terapi
                    </flux:heading>
                </div>
                <flux:separator class="mt-4 mb-4"></flux:separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:heading>ID</flux:heading>
                        <flux:text>{{$therapy->id}}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Status</flux:heading>
                        <flux:text>{{$therapy->status->label()}}</flux:text>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <flux:heading>Tanggal Mulai</flux:heading>
                        <flux:text>{{$therapy->start_date->isoFormat('D MMMM Y')}}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Tanggal Selesai</flux:heading>
                        <flux:text>{{$therapy->end_date->isoFormat('D MMMM Y')}}</flux:text>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <flux:heading>Biaya Jasa Psikolog</flux:heading>
                        <flux:text>@currency($therapy->doctor_fee)</flux:text>
                    </div>
                    <div>
                        <flux:heading>Biaya Jasa Aplikasi</flux:heading>
                        <flux:text>@currency($therapy->application_fee)</flux:text>
                    </div>
                </div>
            </div>

            <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
                <div class="flex items-center space-x-2">
                    <flux:icon.user></flux:icon.user>
                    <flux:heading>
                        Pasien
                    </flux:heading>
                </div>

                <flux:separator class="mt-4 mb-4"></flux:separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <flux:heading>Nama</flux:heading>
                        <flux:text>{{$therapy->patient->name}}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Email</flux:heading>
                        <flux:text>{{$therapy->patient->email}}</flux:text>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <flux:heading>Usia</flux:heading>
                        <flux:text>{{$therapy->patient->age}}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Gender</flux:heading>
                        <flux:text>{{$therapy->patient->gender->label()}}</flux:text>
                    </div>
                </div>
                <div class="mt-4">
                    <flux:heading>Gangguan Lainnya</flux:heading>
                    <flux:text>{{$problems}}</flux:text>
                </div>
                {{--                    <div class="mt-4">--}}
                {{--                        <flux:button--}}
                {{--                            href="{{route('doctor.therapies.in_progress.chat')}}"--}}
                {{--                            icon:trailing="arrow-up-right"--}}
                {{--                            class="w-full"--}}
                {{--                            wire:navigate--}}
                {{--                        >--}}
                {{--                            Chat dengan pasien--}}
                {{--                        </flux:button>--}}
                {{--                    </div>--}}
            </div>
        @else
            <flux:heading>
                Belum ada terapi yang berlangsung
            </flux:heading>
        @endif
    </div>

</section>

