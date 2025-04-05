<?php

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    public bool $isOnline = false;
    public ?int $patientID = null;

    public function checkPatientOnlineStatus()
    {
        $this->isOnline = User::select('id','is_online')->where('id', $this->patientID)->value('is_online');

    }

    public function with()
    {
        $therapy = Therapy::with('patient')->where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $this->patientID = $therapy->patient->id;

        return [
            'therapy' => $therapy,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Berlangsung'])
    <x-therapies.on-going-layout>
        <div class="h-[440px] rounded-lg flex flex-col">
            <!-- Header -->
            <div class="dark:bg-zinc-700 p-4 flex items-center gap-3 rounded-t-lg bg-white border dark:border-transparent">
                <div class="flex items-center gap-2" wire:poll.5s.visible="checkPatientOnlineStatus">
                    @if($isOnline)
                        <flux:avatar badge badge:circle badge:color="green" name="{{$therapy->patient->name}}" />
                    @else
                        <flux:avatar badge badge:circle badge:color="zinc" name="{{$therapy->patient->name}}" />
                    @endif
                    <span>{{$therapy->patient->name}}</span>
                </div>
            </div>

            <!-- Chat Body (Scrollable) -->
            <div class="flex-1 p-4 overflow-y-auto space-y-4 custom-scrollbar border dark:border-transparent">
                <!-- Incoming message -->
                <div class="flex items-start space-x-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">Hello! How are you?</p>
                        <span class="text-xs text-gray-500 block text-right mt-1">10:30 AM</span>
                    </div>
                </div>

                <!-- Outgoing message -->
                <div class="flex items-start space-x-2 justify-end">
                    <div class="bg-green-500 text-white p-3 rounded-lg max-w-xs">
                        <p class="text-sm">Hey! I'm good, thanks for asking. What about you?</p>
                        <span class="text-xs text-gray-200 block text-right mt-1">10:32 AM</span>
                    </div>
                </div>
                <!-- Incoming message -->
                <div class="flex items-start space-x-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">Hello! How are you?</p>
                        <span class="text-xs text-gray-500 block text-right mt-1">10:30 AM</span>
                    </div>
                </div>
                <!-- Incoming message -->
                <div class="flex items-start space-x-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">Hello! How are you?</p>
                        <span class="text-xs text-gray-500 block text-right mt-1">10:30 AM</span>
                    </div>
                </div>
                <!-- Incoming message -->
                <div class="flex items-start space-x-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">Hello! How are you?</p>
                        <span class="text-xs text-gray-500 block text-right mt-1">10:30 AM</span>
                    </div>
                </div>
                <!-- Incoming message -->
                <div class="flex items-start space-x-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">Hello! How are you?</p>
                        <span class="text-xs text-gray-500 block text-right mt-1">10:30 AM</span>
                    </div>
                </div>
            </div>

            <!-- Input Field -->
            <div class="p-3 flex items-center gap-2 bg-white border dark:bg-zinc-700 dark:border-transparent rounded-b-lg">
                <input type="text"
                       class="flex-1 p-2 border rounded-lg text-sm outline-none focus:ring"
                       placeholder="Tulis sebuah pesan...">
            </div>
        </div>
    </x-therapies.on-going-layout>
</section>
