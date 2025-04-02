<?php

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    public $date = null;
    public ?string $link = null;
    public ?string $title = null;
    public ?string $note = null;

    public function with()
    {
        $therapy = Therapy::where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $schedules = TherapySchedule::where('therapy_id', $therapy->id)->get();

        return [
            'schedules' => $schedules,
        ];
    }

    public function resetEdit()
    {
        $this->reset(['date','link']);
        $this->resetValidation(['date','link']);
    }

    public function editSchedule(int $scheduleID)
    {
        $schedule = TherapySchedule::find($scheduleID);
        if (!$schedule) {
            Session::flash('status', ['message' => 'Jadwal terapi tidak dapat ditemukan.', 'success' => false]);
        }

        $this->date = $schedule->date;
        $this->link = $schedule->link;
        $this->title = $schedule->title;
        $this->note = $schedule->note;

        $this->modal('editSchedule')->show();
    }

    public function updateSchedule(int $scheduleID)
    {
        $validated = $this->validate([
            'date' => ['required','date'],
            'link' => ['required','string'],
            'note' => ['required','string'],
        ]);
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Jadwal'])
    <x-therapies.on-going-layout>
        <flux:modal name="editSchedule" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6" x-data="{showNote: false}">
                <form>
                    <div>
                        <flux:heading size="lg">Ubah Jadwal</flux:heading>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <flux:input readonly variant="filled" label="Judul" value="{{$title}}"></flux:input>
                        <flux:input wire:model="date" label="Tanggal dan Waktu"
                                    type="datetime-local"></flux:input>
                    </div>
                    <div class="mt-5">
                        <flux:input wire:model="link"
                                    label="Link video konferensi"></flux:input>
                    </div>
                    <div class="mt-5">
                        <flux:checkbox label="Telah dilaksanakan?" x-model="showNote"/>
                    </div>
                    <div class="mt-5" x-show="showNote">
                        <flux:textarea wire:model="note" label="Catatan hasil sesi terapi untuk pasien"></flux:textarea>
                    </div>
                    <div class="mt-5">
                        <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
        @foreach($schedules as $schedule)
            <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700 mb-5" x-data="{showDesc: false}">
                <div class="flex items-center justify-between flex-wrap gap-y-2">
                    <flux:heading size="lg">{{$schedule->title}}</flux:heading>
                    <div class="flex gap-x-3">
                        <flux:button size="xs" icon="pencil-square" wire:click="editSchedule({{$schedule->id}})"></flux:button>
                    </div>
                </div>
                <div class="mt-5">
                    <flux:input icon="link" value="{{$schedule->link ?? '-'}}" readonly copyable/>
                </div>
                <flux:text class="mt-5">{{$schedule->date}}</flux:text>
                <div class="mt-5">
                    <flux:button class="w-full" @click="showDesc = !showDesc">Deskripsi</flux:button>
                </div>
                <div x-show="showDesc" x-transition class="mt-5">
                    <ul class="list-disc list-inside">
                        @foreach(json_decode($schedule->description) as $description)
                                <flux:text class="text-xs">
                                    <li>
                                    {{$description}}
                                    </li>
                                </flux:text>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach

    </x-therapies.on-going-layout>
</section>
