<?php

use App\Enum\Problem;
use App\Enum\TherapyStatus;
use App\Models\Therapy;
use Livewire\Volt\Component;

new class extends Component {

    public function with()
    {
        $therapy = Therapy::with('patient')->where('status', TherapyStatus::IN_PROGRESS->value)->first();
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input readonly variant="filled" :value="$therapy->id" label="ID"/>
                <flux:input readonly variant="filled" :value="$therapy->status->label()" label="Status"/>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <flux:input readonly variant="filled" value="{{$therapy->start_date}}" label="Tanggal Mulai"/>
                <flux:input readonly variant="filled" value="{{$therapy->end_date}}" label="Tanggal Selesai"/>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <flux:input readonly variant="filled" value="Rp {{$therapy->doctor_fee}}" label="Biaya Jasa Psikolog"/>
                <flux:input readonly variant="filled" value="Rp {{$therapy->application_fee}}" label="Biaya Jasa Aplikasi"/>
            </div>
            <flux:separator class="mt-5 mb-5"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input readonly variant="filled" :value="$therapy->patient->name" label="Nama"/>
                <flux:input readonly variant="filled" :value="$therapy->patient->email" label="Email"/>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <flux:input readonly variant="filled" :value="$therapy->patient->age" label="Umur"/>
                <flux:input readonly variant="filled" :value="$therapy->patient->gender->label()" label="Gender"/>
            </div>
            <div class="mt-5">
                <flux:input readonly variant="filled" :value="$problems" label="Gangguan Lainnya"/>
            </div>
            <div class="mt-5">
                <flux:button
                    href="{{route('doctor.therapies.in_progress.chat')}}"
                    icon:trailing="arrow-up-right"
                    class="w-full"
                >
                    Chat dengan pasien
                </flux:button>
            </div>
        </div>

    </x-therapies.on-going-layout>
</section>

