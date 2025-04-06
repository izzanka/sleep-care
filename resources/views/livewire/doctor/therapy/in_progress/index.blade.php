<?php

use App\Enum\Problem;
use App\Enum\TherapyStatus;
use App\Models\Therapy;
use Livewire\Volt\Component;

new class extends Component {

    public function with()
    {
        $doctorID = auth()->user()->load('doctor')->doctor->id;
        $therapy = Therapy::where('doctor_id', $doctorID)->with('patient')->where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $problems = collect(json_decode($therapy->patient->problems))->map(fn($problem) => Problem::tryFrom($problem)->label())->implode(', ');

        return [
            'therapy' => $therapy,
            'problems' => $problems,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Berlangsung'])
    <x-therapies.on-going-layout>
        <div>
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
                        <flux:text>{{$therapy->start_date}}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Tanggal Selesai</flux:heading>
                        <flux:text>{{$therapy->end_date}}</flux:text>
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
                <div class="mt-4">
                    <flux:button
                        href="{{route('doctor.therapies.in_progress.chat')}}"
                        icon:trailing="arrow-up-right"
                        class="w-full"
                        wire:navigate
                    >
                        Chat dengan pasien
                    </flux:button>
                </div>
            </div>
{{--            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->id" label="ID"/>--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->status->label()" label="Status"/>--}}
{{--            </div>--}}
{{--            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">--}}
{{--                <flux:input readonly variant="filled" value="{{$therapy->start_date}}" label="Tanggal Mulai"/>--}}
{{--                <flux:input readonly variant="filled" value="{{$therapy->end_date}}" label="Tanggal Selesai"/>--}}
{{--            </div>--}}
{{--            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">--}}
{{--                <flux:input readonly variant="filled" value="Rp {{$therapy->doctor_fee}}" label="Biaya Jasa Psikolog"/>--}}
{{--                <flux:input readonly variant="filled" value="Rp {{$therapy->application_fee}}" label="Biaya Jasa Aplikasi"/>--}}
{{--            </div>--}}
{{--            <flux:separator class="mt-5 mb-5"/>--}}
{{--            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->patient->name" label="Nama"/>--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->patient->email" label="Email"/>--}}
{{--            </div>--}}
{{--            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->patient->age" label="Umur"/>--}}
{{--                <flux:input readonly variant="filled" :value="$therapy->patient->gender->label()" label="Gender"/>--}}
{{--            </div>--}}
{{--            <div class="mt-5">--}}
{{--                <flux:input readonly variant="filled" :value="$problems" label="Gangguan Lainnya"/>--}}
{{--            </div>--}}
{{--            <div class="mt-5">--}}
{{--                <flux:button--}}
{{--                    href="{{route('doctor.therapies.in_progress.chat')}}"--}}
{{--                    icon:trailing="arrow-up-right"--}}
{{--                    class="w-full"--}}
{{--                >--}}
{{--                    Chat dengan pasien--}}
{{--                </flux:button>--}}
{{--            </div>--}}
        </div>

    </x-therapies.on-going-layout>
</section>

